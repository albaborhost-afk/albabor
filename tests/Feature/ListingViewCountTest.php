<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Compteur de vues : une vue par visiteur et par jour, et surtout jamais
 * d'erreur — la deuxième visite du jour renvoyait 500 sur SQLite.
 */
class ListingViewCountTest extends TestCase
{
    use RefreshDatabase;

    private function listing(): Listing
    {
        return Listing::create([
            'user_id'         => User::factory()->create()->id,
            'title'           => 'Bateau de peche 6m',
            'description'     => 'En bon etat.',
            'category'        => 'boat',
            'type'            => 'bateau_peche',
            'price_dzd'       => 1_500_000,
            'currency'        => 'DZD',
            'etat'            => 'bon_etat',
            'status'          => 'active',
            'published_until' => now()->addYear(),
        ]);
    }

    public function test_the_same_visitor_counts_once_a_day_and_never_breaks_the_page(): void
    {
        $listing = $this->listing();

        $this->assertTrue(ListingView::recordView($listing, null, '10.0.0.1'));
        $this->assertFalse(ListingView::recordView($listing, null, '10.0.0.1'));
        $this->assertTrue(ListingView::recordView($listing, null, '10.0.0.2'));

        $this->assertSame(2, $listing->fresh()->views_count);
        $this->assertSame(2, ListingView::count());
    }

    public function test_opening_the_same_listing_twice_in_a_day_works_on_the_site_and_the_api(): void
    {
        $this->withoutVite();
        $listing = $this->listing();

        $this->get(route('listings.show', $listing))->assertOk();
        $this->get(route('listings.show', $listing))->assertOk();
        $this->getJson('/api/v1/listings/'.$listing->id)->assertOk();
        $this->getJson('/api/v1/listings/'.$listing->id)->assertOk();

        $this->assertSame(1, $listing->fresh()->views_count);
    }

    public function test_a_lost_race_on_the_first_view_is_not_an_error(): void
    {
        $listing = $this->listing();

        // Une autre requête a inséré la ligne entre la vérification et l'insertion.
        ListingView::create([
            'listing_id' => $listing->id,
            'ip_hash'    => hash('sha256', '10.0.0.1'),
            'view_date'  => now()->toDateString(),
        ]);

        $this->assertFalse(ListingView::recordView($listing, null, '10.0.0.1'));
        $this->assertSame(0, $listing->fresh()->views_count);
    }
}
