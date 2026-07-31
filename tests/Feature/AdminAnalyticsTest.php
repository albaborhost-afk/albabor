<?php

namespace Tests\Feature;

use App\Filament\Widgets\AnalyticsOverviewWidget;
use App\Filament\Widgets\BannerPerformanceWidget;
use App\Filament\Widgets\SiteTrafficChartWidget;
use App\Filament\Widgets\TopListingsByViewsWidget;
use App\Filament\Widgets\TopPayingUsersWidget;
use App\Models\Banner;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Statistiques de l'administration : fréquentation du site, paiements par
 * client, vues par annonce et rendement des panneaux publicitaires.
 */
class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000099',
        ]);
    }

    private function listing(User $seller, array $attributes = []): Listing
    {
        return Listing::create(array_merge([
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
        ], $attributes));
    }

    // ── Fréquentation du site ────────────────────────────────────────────────

    public function test_a_page_view_is_recorded(): void
    {
        $this->withoutVite();

        $this->get(route('home'))->assertOk();

        $this->assertSame(1, SiteVisit::count());
        $this->assertSame(1, SiteVisit::uniqueVisitorsOn(now()->toDateString()));
        $this->assertSame(1, SiteVisit::pageViewsOn(now()->toDateString()));
    }

    public function test_the_same_visitor_counts_once_but_pages_add_up(): void
    {
        $this->withoutVite();

        $this->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk();
        $this->get(route('listings.index'))->assertOk();

        $this->assertSame(1, SiteVisit::count(), 'Un visiteur ne doit compter qu\'une fois par jour.');
        $this->assertSame(3, SiteVisit::pageViewsOn(now()->toDateString()));
    }

    public function test_admin_pages_are_not_counted_as_visits(): void
    {
        $this->actingAs($this->admin())->get('/admin/statistiques');

        $this->assertSame(0, SiteVisit::count(), 'Le panel admin gonflerait les chiffres.');
    }

    public function test_bots_are_not_counted(): void
    {
        $this->withoutVite();

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])
            ->get(route('home'))
            ->assertOk();

        $this->assertSame(0, SiteVisit::count());
    }

    public function test_a_missing_page_is_not_counted(): void
    {
        $this->get('/une-page-qui-nexiste-pas')->assertNotFound();

        $this->assertSame(0, SiteVisit::count());
    }

    public function test_the_visit_stores_no_raw_ip(): void
    {
        $this->withoutVite();

        $this->get(route('home'))->assertOk();

        $visit = SiteVisit::sole();

        $this->assertSame(64, strlen($visit->visitor_hash), 'Le hachage SHA-256 fait 64 caractères.');
        $this->assertStringNotContainsString('127.0.0.1', $visit->visitor_hash);
    }

    public function test_a_logged_in_visitor_is_linked_to_their_account(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['phone' => '+213670000001']);

        $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertSame($user->id, SiteVisit::sole()->user_id);
    }

    // ── Panneaux publicitaires ───────────────────────────────────────────────

    public function test_the_website_counts_banner_impressions(): void
    {
        $this->withoutVite();

        $banner = Banner::create([
            'title'      => 'Chantier naval Alger',
            'image_path' => 'banners/test.jpg',
            'is_active'  => true,
            'position'   => 1,
        ]);

        $this->get(route('home'))->assertOk();

        $this->assertSame(1, $banner->fresh()->view_count);
    }

    public function test_the_click_through_rate_is_computed(): void
    {
        $banner = Banner::create([
            'title'       => 'Chantier naval Alger',
            'image_path'  => 'banners/test.jpg',
            'is_active'   => true,
            'position'    => 1,
            'view_count'  => 1000,
            'click_count' => 25,
        ]);

        $this->assertSame(2.5, $banner->click_through_rate);
    }

    public function test_a_banner_never_shown_has_no_rate_instead_of_dividing_by_zero(): void
    {
        $banner = Banner::create([
            'title'      => 'Nouvelle banniere',
            'image_path' => 'banners/test.jpg',
            'is_active'  => true,
            'position'   => 1,
        ]);

        $this->assertSame(0.0, $banner->click_through_rate);
    }

    // ── Page et widgets ──────────────────────────────────────────────────────

    public function test_the_statistics_page_opens_for_an_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/statistiques')
            ->assertOk()
            ->assertSee('Statistiques de la plateforme');
    }

    public function test_a_normal_user_cannot_open_the_admin_statistics(): void
    {
        $user = User::factory()->create(['phone' => '+213670000002']);

        $response = $this->actingAs($user)->get('/admin/statistiques');

        $this->assertContains($response->getStatusCode(), [403, 404, 302]);
    }

    public function test_every_widget_renders_with_real_data(): void
    {
        $admin  = $this->admin();
        $seller = User::factory()->create(['phone' => '+213670000003']);
        $buyer  = User::factory()->create(['phone' => '+213670000004']);

        $listing = $this->listing($seller, ['views_count' => 42]);

        Payment::create([
            'user_id'    => $buyer->id,
            'listing_id' => $listing->id,
            'type'       => 'publish_listing',
            'amount_dzd' => 5000,
            'method'     => 'baridimob',
            'status'     => 'approved',
        ]);

        Banner::create([
            'title'       => 'Chantier naval Alger',
            'image_path'  => 'banners/test.jpg',
            'is_active'   => true,
            'position'    => 1,
            'view_count'  => 500,
            'click_count' => 10,
        ]);

        SiteVisit::record('10.0.0.1', 'Mozilla/5.0', null);

        $this->actingAs($admin);

        foreach ([
            AnalyticsOverviewWidget::class,
            SiteTrafficChartWidget::class,
            TopPayingUsersWidget::class,
            TopListingsByViewsWidget::class,
            BannerPerformanceWidget::class,
        ] as $widget) {
            Livewire::test($widget)->assertOk();
        }
    }

    public function test_the_paying_customers_table_totals_only_approved_payments(): void
    {
        $admin  = $this->admin();
        $seller = User::factory()->create(['phone' => '+213670000005']);
        $buyer  = User::factory()->create(['phone' => '+213670000006']);

        $listing = $this->listing($seller);

        foreach ([['approved', 5000], ['approved', 12000], ['pending', 99000]] as [$status, $amount]) {
            Payment::create([
                'user_id'    => $buyer->id,
                'listing_id' => $listing->id,
                'type'       => 'publish_listing',
                'amount_dzd' => $amount,
                'method'     => 'baridimob',
                'status'     => $status,
            ]);
        }

        $this->actingAs($admin);

        $rows = Livewire::test(TopPayingUsersWidget::class)
            ->assertOk()
            ->instance()
            ->getTableRecords();

        $row = $rows->firstWhere('id', $buyer->id);

        $this->assertNotNull($row, 'Le client ayant payé doit apparaître.');
        $this->assertSame(17000, (int) $row->total_paid, 'Le paiement en attente ne doit pas compter.');
        $this->assertSame(2, (int) $row->approved_payments_count);
    }

    public function test_a_user_without_approved_payments_is_absent(): void
    {
        $admin = $this->admin();
        User::factory()->create(['phone' => '+213670000007']);

        $this->actingAs($admin);

        $rows = Livewire::test(TopPayingUsersWidget::class)
            ->assertOk()
            ->instance()
            ->getTableRecords();

        $this->assertCount(0, $rows);
    }

    public function test_listings_are_ranked_by_views(): void
    {
        $admin  = $this->admin();
        $seller = User::factory()->create(['phone' => '+213670000008']);

        $quiet   = $this->listing($seller, ['title' => 'Annonce peu vue', 'views_count' => 3]);
        $popular = $this->listing($seller, ['title' => 'Annonce populaire', 'views_count' => 900]);

        $this->actingAs($admin);

        $rows = Livewire::test(TopListingsByViewsWidget::class)
            ->assertOk()
            ->instance()
            ->getTableRecords();

        $this->assertSame($popular->id, $rows->first()->id);
        $this->assertTrue($rows->contains('id', $quiet->id));
    }
}
