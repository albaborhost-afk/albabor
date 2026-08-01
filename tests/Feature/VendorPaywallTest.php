<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Paywall vendeur : publier un moteur ou une pièce exige un abonnement actif.
 *
 * `hasActiveSubscription()` existait mais n'était appelée nulle part. Comme
 * passer son compte en « vendeur » est gratuit et instantané, n'importe qui
 * pouvait publier des moteurs et des pièces sans jamais payer — la règle de
 * monétisation décrite dans CLAUDE.md était entièrement contournée.
 */
class VendorPaywallTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.listing_disk', 'public'));
    }

    private function vendor(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'account_type' => 'vendor',
            'phone'        => '+213670000070',
        ], $attributes));
    }

    private function giveActiveSubscription(User $user): Subscription
    {
        $plan = Plan::create([
            'name'            => 'Vendeur mensuel',
            'price_dzd'       => 3000,
            'duration_days'   => 30,
            'is_active'       => true,
        ]);

        return Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'starts_at'  => now()->subDay(),
            'ends_at'    => now()->addDays(29),
        ]);
    }

    private function partsPayload(): array
    {
        return [
            'title'       => 'Helice inox 3 pales',
            'description' => 'Neuve, jamais montee.',
            'category'    => 'parts',
            'price_dzd'   => 45000,
            'currency'    => 'DZD',
            'type_offre'  => 'negociable',
            'etat'        => 'jamais_utilise',
            'wilaya'      => 'Alger',
            'images'      => [UploadedFile::fake()->image('piece.jpg', 800, 600)],
        ];
    }

    // ── La règle ─────────────────────────────────────────────────────────────

    public function test_a_vendor_without_a_subscription_cannot_publish_parts(): void
    {
        $vendor = $this->vendor();

        $this->assertFalse($vendor->canPublishEngineOrParts());
    }

    public function test_a_vendor_with_an_active_subscription_can(): void
    {
        $vendor = $this->vendor();
        $this->giveActiveSubscription($vendor);

        $this->assertTrue($vendor->fresh()->canPublishEngineOrParts());
    }

    public function test_an_expired_subscription_does_not_count(): void
    {
        $vendor = $this->vendor();
        $subscription = $this->giveActiveSubscription($vendor);
        $subscription->update(['ends_at' => now()->subDay()]);

        $this->assertFalse($vendor->fresh()->canPublishEngineOrParts());
    }

    public function test_a_cancelled_subscription_does_not_count(): void
    {
        $vendor = $this->vendor();
        $subscription = $this->giveActiveSubscription($vendor);
        $subscription->update(['status' => 'cancelled']);

        $this->assertFalse($vendor->fresh()->canPublishEngineOrParts());
    }

    public function test_a_plain_user_still_cannot(): void
    {
        $user = User::factory()->create(['phone' => '+213670000071']);

        $this->assertFalse($user->canPublishEngineOrParts());
    }

    // ── Les dérogations ──────────────────────────────────────────────────────

    public function test_the_free_publishing_flag_still_bypasses_the_paywall(): void
    {
        // C'est l'échappatoire de l'administration pour un compte au cas par cas.
        $vendor = $this->vendor(['free_publishing' => true]);

        $this->assertTrue($vendor->canPublishEngineOrParts());
    }

    public function test_an_admin_is_never_blocked(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000072',
        ]);

        $this->assertTrue($admin->canPublishEngineOrParts());
    }

    // ── Application effective ────────────────────────────────────────────────

    public function test_the_website_refuses_the_submission(): void
    {
        $vendor = $this->vendor();

        $this->actingAs($vendor)
            ->postJson(route('listings.store'), $this->partsPayload())
            ->assertStatus(422);

        $this->assertSame(0, $vendor->listings()->count());
    }

    public function test_the_api_refuses_the_submission(): void
    {
        $vendor = $this->vendor();

        $this->actingAs($vendor, 'sanctum')
            ->postJson('/api/v1/listings', $this->partsPayload())
            ->assertStatus(403);

        $this->assertSame(0, $vendor->listings()->count());
    }

    public function test_a_subscribed_vendor_gets_through(): void
    {
        $vendor = $this->vendor();
        $this->giveActiveSubscription($vendor);

        $this->actingAs($vendor->fresh())
            ->postJson(route('listings.store'), $this->partsPayload())
            ->assertOk();

        $this->assertSame(1, $vendor->listings()->count());
    }

    public function test_boats_are_not_affected_by_the_paywall(): void
    {
        $vendor = $this->vendor();

        $payload = array_merge($this->partsPayload(), [
            'title'    => 'Bateau de peche 6m',
            'category' => 'boat',
            'type'     => 'bateau_peche',
            'etat'     => 'bon_etat',
        ]);

        // Les bateaux relèvent du paiement à la publication, pas de l'abonnement.
        $this->actingAs($vendor)
            ->postJson(route('listings.store'), $payload)
            ->assertOk();
    }
}
