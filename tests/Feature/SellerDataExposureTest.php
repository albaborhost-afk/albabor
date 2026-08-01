<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ce qu'un tiers reçoit d'un compte.
 *
 * `GET /api/v1/listings` est public et sérialise le vendeur : l'adresse
 * e-mail de chaque vendeur du site était récupérable sans compte, en une
 * requête, avec en prime des indicateurs internes (compte bloqué,
 * publication gratuite, identifiant Google, photo en base64).
 */
class SellerDataExposureTest extends TestCase
{
    use RefreshDatabase;

    private const HIDDEN_FROM_STRANGERS = [
        'email',
        'email_verified_at',
        'google_id',
        'is_blocked',
        'free_publishing',
        'profile_picture_data',
        'password',
        'remember_token',
    ];

    private function seller(): User
    {
        return User::factory()->create([
            'name'      => 'Karim Benali',
            'email'     => 'karim@exemple.com',
            'phone'     => '+213670000040',
            'google_id' => 'google-123',
        ]);
    }

    private function activeListingFor(User $seller): Listing
    {
        return Listing::create([
            'user_id'         => $seller->id,
            'title'           => 'Bateau de peche 6m',
            'description'     => 'En bon etat.',
            'category'        => 'boat',
            'price_dzd'       => 1_500_000,
            'currency'        => 'DZD',
            'type_offre'      => 'negociable',
            'etat'            => 'bon_etat',
            'status'          => 'active',
            'published_until' => now()->addYear(),
        ]);
    }

    public function test_the_public_listing_api_does_not_expose_the_seller_email(): void
    {
        $seller = $this->seller();
        $this->activeListingFor($seller);

        $response = $this->getJson('/api/v1/listings')->assertOk();

        $user = $response->json('data.0.user');

        $this->assertNotNull($user, 'Le vendeur doit bien être présent dans la réponse.');
        $this->assertSame('Karim Benali', $user['name'], 'Le nom reste public.');

        foreach (self::HIDDEN_FROM_STRANGERS as $field) {
            $this->assertArrayNotHasKey($field, $user, "Le champ « {$field} » ne doit pas sortir.");
        }
    }

    public function test_the_public_listing_detail_does_not_expose_it_either(): void
    {
        $seller  = $this->seller();
        $listing = $this->activeListingFor($seller);

        $user = $this->getJson('/api/v1/listings/' . $listing->id)
            ->assertOk()
            ->json('listing.user');

        foreach (self::HIDDEN_FROM_STRANGERS as $field) {
            $this->assertArrayNotHasKey($field, $user);
        }
    }

    public function test_the_public_vendor_profile_does_not_expose_it_either(): void
    {
        $seller = $this->seller();
        $this->activeListingFor($seller);

        $response = $this->getJson('/api/v1/vendors/' . $seller->id)->assertOk();

        $this->assertArrayNotHasKey('email', $response->json('user'));
    }

    public function test_another_logged_in_user_still_does_not_see_it(): void
    {
        $seller = $this->seller();
        $this->activeListingFor($seller);

        $other = User::factory()->create(['phone' => '+213670000041']);

        $user = $this->actingAs($other, 'sanctum')
            ->getJson('/api/v1/listings')
            ->assertOk()
            ->json('data.0.user');

        $this->assertArrayNotHasKey('email', $user);
    }

    // ── Ce qui doit continuer de fonctionner ─────────────────────────────────

    public function test_the_account_still_gets_its_own_email(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('user.email', 'karim@exemple.com');
    }

    public function test_logging_in_still_returns_the_email(): void
    {
        User::factory()->create([
            'email'    => 'connexion@exemple.com',
            'password' => 'motdepasse123',
            'phone'    => '+213670000042',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => 'connexion@exemple.com',
            'password' => 'motdepasse123',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'connexion@exemple.com');
    }

    public function test_an_admin_still_sees_everything(): void
    {
        $seller = $this->seller();
        $this->activeListingFor($seller);

        $admin = User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000043',
        ]);

        $user = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/listings')
            ->assertOk()
            ->json('data.0.user');

        $this->assertSame('karim@exemple.com', $user['email']);
    }

    public function test_the_base64_picture_never_leaves_even_for_the_account(): void
    {
        $seller = $this->seller();
        $seller->forceFill(['profile_picture_data' => 'data:image/jpeg;base64,AAAA'])->save();

        $user = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->json('user');

        // Inutile au client — `profile_picture_url` suffit — et cela alourdit
        // chaque réponse contenant ce compte.
        $this->assertArrayNotHasKey('profile_picture_data', $user);
        $this->assertNotNull($user['profile_picture_url']);
    }
}
