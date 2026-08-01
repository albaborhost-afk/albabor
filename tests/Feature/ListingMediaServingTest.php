<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Diffusion des photos d'annonces.
 *
 * Chaque vignette passait par PHP — exists() + mimeType() + get(), soit trois
 * allers-retours S3 et le fichier entier en mémoire dans un processus FPM.
 * Une page de résultats saturait le pool et provoquait des « upstream timed
 * out ». Sur S3 on redirige désormais vers un lien signé ; le contrôle
 * d'accès, lui, doit rester intact.
 */
class ListingMediaServingTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = config('filesystems.listing_disk', 'public');
        Storage::fake($this->disk);
    }

    private function seller(): User
    {
        return User::factory()->create(['phone' => '+213670000020']);
    }

    private function listingFor(User $seller, string $status = 'active'): Listing
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
            'status'          => $status,
            'published_until' => now()->addYear(),
        ]);
    }

    private function mediaFor(Listing $listing, string $extension = 'jpg'): ListingMedia
    {
        $path      = "listings/{$listing->id}/photo.{$extension}";
        $thumbPath = "listings/{$listing->id}/thumb_photo.{$extension}";

        Storage::disk($this->disk)->put($path, 'image-principale');
        Storage::disk($this->disk)->put($thumbPath, 'vignette');

        return ListingMedia::create([
            'listing_id'     => $listing->id,
            'path'           => $path,
            'thumbnail_path' => $thumbPath,
            'order'          => 1,
        ]);
    }

    public function test_an_image_of_an_active_listing_is_served(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller()));

        $response = $this->get(route('listing-media.show', ['media' => $media->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame('image-principale', $response->getContent());
    }

    public function test_the_thumbnail_variant_is_served(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller()));

        $response = $this->get(route('listing-media.show', ['media' => $media->id, 'variant' => 'thumb']));

        $response->assertOk();
        $this->assertSame('vignette', $response->getContent());
    }

    public function test_the_content_type_follows_the_extension(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller()), 'png');

        $this->get(route('listing-media.show', ['media' => $media->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_an_unknown_variant_is_refused(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller()));

        $this->get(route('listing-media.show', ['media' => $media->id, 'variant' => 'original']))
            ->assertNotFound();
    }

    public function test_a_missing_file_gives_404_instead_of_an_error(): void
    {
        $listing = $this->listingFor($this->seller());

        $media = ListingMedia::create([
            'listing_id' => $listing->id,
            'path'       => "listings/{$listing->id}/absente.jpg",
            'order'      => 1,
        ]);

        $this->get(route('listing-media.show', ['media' => $media->id]))->assertNotFound();
    }

    // ── Contrôle d'accès : il ne doit pas avoir bougé ────────────────────────

    public function test_a_stranger_cannot_see_the_photo_of_a_non_active_listing(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller(), 'pending_review'));

        $this->get(route('listing-media.show', ['media' => $media->id]))->assertNotFound();

        $other = User::factory()->create(['phone' => '+213670000021']);
        $this->actingAs($other)
            ->get(route('listing-media.show', ['media' => $media->id]))
            ->assertNotFound();
    }

    public function test_the_owner_still_sees_the_photo_of_a_non_active_listing(): void
    {
        $seller = $this->seller();
        $media  = $this->mediaFor($this->listingFor($seller, 'pending_review'));

        $this->actingAs($seller)
            ->get(route('listing-media.show', ['media' => $media->id]))
            ->assertOk();
    }

    public function test_an_admin_sees_the_photo_of_a_non_active_listing(): void
    {
        $media = $this->mediaFor($this->listingFor($this->seller(), 'pending_review'));

        $admin = User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000022',
        ]);

        $this->actingAs($admin)
            ->get(route('listing-media.show', ['media' => $media->id]))
            ->assertOk();
    }

    public function test_a_traversal_path_is_refused(): void
    {
        $listing = $this->listingFor($this->seller());

        $media = ListingMedia::create([
            'listing_id' => $listing->id,
            'path'       => '../../.env',
            'order'      => 1,
        ]);

        $this->get(route('listing-media.show', ['media' => $media->id]))->assertNotFound();
    }

    public function test_the_edit_page_removes_orphaned_media_records(): void
    {
        $this->withoutVite();

        $seller  = $this->seller();
        $listing = $this->listingFor($seller);

        $kept = $this->mediaFor($listing);

        $orphan = ListingMedia::create([
            'listing_id' => $listing->id,
            'path'       => "listings/{$listing->id}/disparue.jpg",
            'order'      => 2,
        ]);

        $this->actingAs($seller)->get(route('listings.edit', $listing))->assertOk();

        // Le nettoyage se fait maintenant à partir d'un seul listing du dossier
        // plutôt que d'un exists() par photo : le résultat doit être identique.
        $this->assertDatabaseHas('listing_media', ['id' => $kept->id]);
        $this->assertDatabaseMissing('listing_media', ['id' => $orphan->id]);
    }
}
