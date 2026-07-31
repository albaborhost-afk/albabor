<?php

namespace Tests\Feature;

use App\Filament\Resources\BannerRequestResource\Pages\ListBannerRequests;
use App\Models\BannerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Demande d'espace publicitaire : formulaire public → suivi côté administration.
 */
class BannerRequestTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'contact_name' => 'Sofiane Mekki',
            'company_name' => 'Chantier naval Alger',
            'email'        => 'Sofiane@Exemple.COM',
            'whatsapp'     => '+213670000000',
            'message'      => 'Nous souhaitons annoncer nos moteurs hors-bord pendant deux mois.',
            'budget_dzd'   => 30000,
        ], $overrides);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000099',
        ]);
    }

    public function test_the_public_page_is_reachable_without_an_account(): void
    {
        $this->withoutVite();

        $this->get(route('publicite.create'))
            ->assertOk()
            ->assertSeeText(__('messages.banner_request_title'));
    }

    public function test_a_visitor_can_send_a_request(): void
    {
        $this->withoutVite();

        $this->post(route('publicite.store'), $this->payload())
            ->assertRedirect(route('publicite.create'))
            ->assertSessionHas('success');

        $request = BannerRequest::sole();

        $this->assertSame('Sofiane Mekki', $request->contact_name);
        $this->assertSame('Chantier naval Alger', $request->company_name);
        $this->assertSame('sofiane@exemple.com', $request->email, 'L\'e-mail est normalisé en minuscules.');
        $this->assertSame(30000, $request->budget_dzd);
        $this->assertSame(BannerRequest::STATUS_NEW, $request->status);
        $this->assertNull($request->user_id);
    }

    public function test_the_whatsapp_number_is_split_into_country_code_and_number(): void
    {
        $this->post(route('publicite.store'), $this->payload())->assertRedirect();

        $request = BannerRequest::sole();

        $this->assertSame('+213', $request->whatsapp_country_code);
        $this->assertSame('+213 670000000', $request->full_whatsapp);
        $this->assertSame('https://wa.me/213670000000', $request->whatsapp_url);
    }

    public function test_a_logged_in_visitor_is_linked_to_the_request(): void
    {
        $user = User::factory()->create(['phone' => '+213670000001']);

        $this->actingAs($user)
            ->post(route('publicite.store'), $this->payload())
            ->assertRedirect();

        $this->assertSame($user->id, BannerRequest::sole()->user_id);
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->post(route('publicite.store'), [])
            ->assertSessionHasErrors(['contact_name', 'email', 'whatsapp', 'message']);

        $this->assertSame(0, BannerRequest::count());
    }

    public function test_a_too_short_message_is_refused(): void
    {
        $this->post(route('publicite.store'), $this->payload(['message' => 'salut']))
            ->assertSessionHasErrors('message');

        $this->assertSame(0, BannerRequest::count());
    }

    public function test_an_invalid_whatsapp_number_is_refused(): void
    {
        $this->post(route('publicite.store'), $this->payload(['whatsapp' => 'pas-un-numero']))
            ->assertSessionHasErrors('whatsapp');

        $this->assertSame(0, BannerRequest::count());
    }

    public function test_the_company_and_budget_stay_optional(): void
    {
        $this->post(route('publicite.store'), $this->payload([
            'company_name' => null,
            'budget_dzd'   => null,
        ]))->assertRedirect();

        $request = BannerRequest::sole();

        $this->assertNull($request->company_name);
        $this->assertNull($request->budget_dzd);
    }

    public function test_a_bot_filling_the_honeypot_is_silently_ignored(): void
    {
        $this->post(route('publicite.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(route('publicite.create'))
            ->assertSessionHas('success');

        $this->assertSame(0, BannerRequest::count(), 'Rien ne doit être enregistré.');
    }

    // ── Administration ───────────────────────────────────────────────────────

    public function test_the_admin_sees_the_request(): void
    {
        $this->post(route('publicite.store'), $this->payload())->assertRedirect();

        $this->actingAs($this->admin())
            ->get('/admin/banner-requests')
            ->assertOk()
            ->assertSee('Sofiane Mekki')
            ->assertSee('sofiane@exemple.com');
    }

    public function test_a_normal_user_cannot_see_the_requests(): void
    {
        $user = User::factory()->create(['phone' => '+213670000002']);

        $response = $this->actingAs($user)->get('/admin/banner-requests');

        $this->assertContains($response->getStatusCode(), [403, 404, 302]);
    }

    public function test_the_navigation_badge_counts_only_new_requests(): void
    {
        $this->post(route('publicite.store'), $this->payload())->assertRedirect();
        $this->post(route('publicite.store'), $this->payload(['email' => 'autre@exemple.com']))->assertRedirect();

        BannerRequest::latest()->first()->update(['status' => BannerRequest::STATUS_CONTACTED]);

        $this->actingAs($this->admin());

        $this->assertSame('1', \App\Filament\Resources\BannerRequestResource::getNavigationBadge());
    }

    public function test_marking_as_contacted_stamps_the_date(): void
    {
        $this->post(route('publicite.store'), $this->payload())->assertRedirect();

        $request = BannerRequest::sole();
        $this->assertNull($request->contacted_at);

        $this->actingAs($this->admin());

        Livewire::test(ListBannerRequests::class)
            ->callTableAction('markContacted', $request)
            ->assertHasNoErrors();

        $request->refresh();

        $this->assertSame(BannerRequest::STATUS_CONTACTED, $request->status);
        $this->assertNotNull($request->contacted_at);
    }

    public function test_the_admin_list_opens_on_the_new_requests_tab(): void
    {
        $this->post(route('publicite.store'), $this->payload())->assertRedirect();

        $handled = BannerRequest::create($this->payload([
            'email'  => 'traite@exemple.com',
            'status' => BannerRequest::STATUS_ACCEPTED,
        ]));

        $this->actingAs($this->admin());

        Livewire::test(ListBannerRequests::class)
            ->assertOk()
            ->assertCanSeeTableRecords(BannerRequest::where('status', BannerRequest::STATUS_NEW)->get())
            ->assertCanNotSeeTableRecords([$handled]);
    }
}
