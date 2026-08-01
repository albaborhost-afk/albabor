<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Deux fonctions terminées mais jamais mises en ligne, désormais livrées :
 * la suppression de compte (exigée par Apple, règle 5.1.1(v)) et
 * l'expiration nocturne des annonces.
 */
class AccountDeletionAndExpiryTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config('filesystems.listing_disk', 'public');
        Storage::fake($this->disk);
    }

    private function userWithListing(): array
    {
        $user = User::factory()->create(['phone' => '+213670000080']);

        $listing = Listing::create([
            'user_id'         => $user->id,
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

        $path = "listings/{$listing->id}/photo.jpg";
        Storage::disk($this->disk)->put($path, 'image');

        ListingMedia::create([
            'listing_id' => $listing->id,
            'path'       => $path,
            'order'      => 1,
        ]);

        return [$user, $listing, $path];
    }

    // ── Suppression de compte ────────────────────────────────────────────────

    public function test_the_route_and_its_method_exist(): void
    {
        // La route était livrée sans sa méthode : tout appel renvoyait 500.
        $user = User::factory()->create(['phone' => '+213670000081']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/profile')
            ->assertOk();
    }

    public function test_deleting_an_account_removes_it_with_its_listings_and_files(): void
    {
        [$user, $listing, $path] = $this->userWithListing();
        $user->createToken('mobile');

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/profile')
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
        Storage::disk($this->disk)->assertMissing($path);
    }

    public function test_an_anonymous_caller_cannot_delete_an_account(): void
    {
        $user = User::factory()->create(['phone' => '+213670000082']);

        $this->deleteJson('/api/v1/profile')->assertStatus(401);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    // ── Expiration nocturne ──────────────────────────────────────────────────

    public function test_an_expired_listing_switches_status(): void
    {
        [, $listing] = $this->userWithListing();
        $listing->update(['published_until' => now()->subDay()]);

        $this->artisan('listings:expire')->assertSuccessful();

        $this->assertSame('expired', $listing->fresh()->status);
    }

    public function test_a_still_valid_listing_is_left_alone(): void
    {
        [, $listing] = $this->userWithListing();

        $this->artisan('listings:expire')->assertSuccessful();

        $this->assertSame('active', $listing->fresh()->status);
    }

    public function test_a_finished_feature_slot_is_cleared(): void
    {
        [, $listing] = $this->userWithListing();
        $listing->update(['featured_until' => now()->subDay()]);

        $this->artisan('listings:expire')->assertSuccessful();

        $this->assertNull($listing->fresh()->featured_until);
    }

    public function test_the_dry_run_changes_nothing(): void
    {
        [, $listing] = $this->userWithListing();
        $listing->update(['published_until' => now()->subDay()]);

        $this->artisan('listings:expire', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('active', $listing->fresh()->status);
    }
}
