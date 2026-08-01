<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Hygiène du site public : plan du site et en-têtes de sécurité.
 */
class SiteHygieneTest extends TestCase
{
    use RefreshDatabase;

    private function activeListing(): Listing
    {
        $seller = User::factory()->create(['phone' => '+213670000030']);

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

    // ── Plan du site ─────────────────────────────────────────────────────────

    public function test_the_sitemap_is_served_as_xml(): void
    {
        Cache::forget('sitemap.xml');

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringStartsWith('<?xml', $response->getContent());
        $response->assertSee('<urlset', false);
    }

    public function test_the_sitemap_lists_active_listings(): void
    {
        Cache::forget('sitemap.xml');

        $listing = $this->activeListing();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('listings.show', $listing->id), false);
    }

    public function test_the_sitemap_hides_listings_that_are_not_active(): void
    {
        $listing = $this->activeListing();
        $listing->update(['status' => 'paused']);

        Cache::forget('sitemap.xml');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('listings.show', $listing->id), false);
    }

    public function test_the_sitemap_contains_the_entry_pages(): void
    {
        Cache::forget('sitemap.xml');

        $response = $this->get('/sitemap.xml')->assertOk();

        foreach ([route('home'), route('listings.index'), route('publicite.create')] as $url) {
            $response->assertSee($url, false);
        }
    }

    public function test_robots_points_to_the_sitemap_and_hides_private_areas(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: https://albabor.com/sitemap.xml', $robots);

        foreach (['/admin', '/profil', '/messages', '/paiements'] as $private) {
            $this->assertStringContainsString("Disallow: {$private}", $robots);
        }
    }

    // ── En-têtes ─────────────────────────────────────────────────────────────

    public function test_the_security_headers_are_present(): void
    {
        $this->withoutVite();

        $response = $this->get(route('home'))->assertOk();

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');

        $this->assertStringContainsString(
            'geolocation=()',
            $response->headers->get('Permissions-Policy') ?? ''
        );
    }

    public function test_the_headers_also_apply_to_the_api(): void
    {
        $this->getJson('/api/v1/settings/exchange-rate')
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
