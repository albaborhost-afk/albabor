<?php

namespace Tests\Feature;

use App\Filament\Resources\ListingResource\Pages\CreateListing;
use App\Filament\Resources\ListingResource\Pages\EditListing;
use App\Filament\Resources\ListingResource\Pages\ListListings;
use App\Filament\Support\ListingOwnerSelect;
use App\Filament\Widgets\PendingActionsWidget;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\User;
use App\Services\ListingOwnership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * À qui appartient une annonce.
 *
 * Des annonces avaient été publiées « au nom d'Albabor » (depuis le compte
 * administrateur) pour des clients sans compte : le site affichait
 * l'administration comme vendeur, et le client n'avait accès ni à son annonce,
 * ni aux messages des acheteurs. Désormais : jamais d'annonce sur un compte
 * administrateur, publication au nom du vendeur depuis l'administration, et
 * transfert des annonces existantes vers le bon compte.
 */
class ListingOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name'         => 'Admin Albabor',
            'account_type' => 'admin',
            'phone'        => '0550000000',
        ]);
    }

    private function seller(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name'               => 'Karim Benali',
            'phone'              => '0676085441',
            'phone_country_code' => '+213',
        ], $attributes));
    }

    private function listing(User $owner, array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'user_id'         => $owner->id,
            'title'           => 'Bateau de peche 6m',
            'description'     => 'En bon etat.',
            'category'        => 'boat',
            'type'            => 'bateau_peche',
            'price_dzd'       => 1_500_000,
            'currency'        => 'DZD',
            'type_offre'      => 'negociable',
            'etat'            => 'bon_etat',
            'status'          => 'active',
            'published_until' => now()->addYear(),
        ], $attributes));
    }

    // ── Transfert ────────────────────────────────────────────────────────────

    public function test_the_admin_transfers_a_listing_with_its_conversations_and_mediations(): void
    {
        $admin   = $this->admin();
        $seller  = $this->seller();
        $buyer   = User::factory()->create(['phone' => '0555000001']);
        $listing = $this->listing($admin, ['client_token' => str_repeat('a', 64)]);

        $conversation = Conversation::create([
            'listing_id'          => $listing->id,
            'buyer_id'            => $buyer->id,
            'seller_id'           => $admin->id,
            'seller_last_read_at' => now(),
        ]);
        $ticket = MediationTicket::create([
            'listing_id' => $listing->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $admin->id,
            'status'     => 'new',
        ]);
        $payment = Payment::create([
            'user_id'    => $admin->id,
            'listing_id' => $listing->id,
            'type'       => 'publish_listing',
            'amount_dzd' => 5000,
            'method'     => 'ccp',
            'proof_path' => 'proofs/x.jpg',
            'status'     => 'approved',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableAction('transfer', $listing, data: ['new_owner_id' => $seller->id])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Annonce transférée');

        $listing->refresh();
        $this->assertSame($seller->id, $listing->user_id);
        $this->assertNull($listing->client_token, 'Le jeton de reprise appartenait à l\'ancien compte.');
        $this->assertSame('active', $listing->status, 'Le statut ne change pas au transfert.');

        $conversation->refresh();
        $this->assertSame($seller->id, $conversation->seller_id);
        $this->assertNull($conversation->seller_last_read_at, 'Le nouveau vendeur n\'a encore rien lu.');
        $this->assertSame($buyer->id, $conversation->buyer_id);

        $this->assertSame($seller->id, $ticket->refresh()->seller_id);

        $this->assertSame($admin->id, $payment->refresh()->user_id, 'Le paiement reste au nom du compte qui a payé.');
    }

    public function test_a_conversation_where_the_new_owner_is_the_buyer_is_left_alone(): void
    {
        $admin   = $this->admin();
        $seller  = $this->seller();
        $listing = $this->listing($admin);

        // Le futur propriétaire avait posé une question sur l'annonce en tant
        // qu'acheteur : après le transfert il ne peut pas être des deux côtés.
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id'   => $seller->id,
            'seller_id'  => $admin->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableAction('transfer', $listing, data: ['new_owner_id' => $seller->id])
            ->assertHasNoTableActionErrors();

        $this->assertSame($seller->id, $listing->refresh()->user_id);
        $this->assertSame($admin->id, $conversation->refresh()->seller_id);
    }

    public function test_a_listing_cannot_be_transferred_to_an_admin_account(): void
    {
        $admin      = $this->admin();
        $otherAdmin = User::factory()->create(['account_type' => 'admin', 'phone' => '0550000002']);
        $seller     = $this->seller();
        $listing    = $this->listing($seller);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableAction('transfer', $listing, data: ['new_owner_id' => $otherAdmin->id])
            ->assertHasTableActionErrors(['new_owner_id']);

        $this->assertSame($seller->id, $listing->refresh()->user_id);
    }

    public function test_a_listing_cannot_be_transferred_to_a_blocked_account(): void
    {
        $admin   = $this->admin();
        $blocked = $this->seller(['is_blocked' => true, 'email' => 'bloque@example.dz']);
        $listing = $this->listing($admin);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableAction('transfer', $listing, data: ['new_owner_id' => $blocked->id])
            ->assertHasTableActionErrors(['new_owner_id']);

        $this->assertSame($admin->id, $listing->refresh()->user_id);
    }

    public function test_transferring_to_the_current_owner_changes_nothing(): void
    {
        $admin   = $this->admin();
        $seller  = $this->seller();
        $listing = $this->listing($seller, ['client_token' => str_repeat('b', 64)]);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableAction('transfer', $listing, data: ['new_owner_id' => $seller->id])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Aucun changement');

        $listing->refresh();
        $this->assertSame($seller->id, $listing->user_id);
        $this->assertSame(str_repeat('b', 64), $listing->client_token);
    }

    public function test_several_listings_are_transferred_at_once(): void
    {
        $admin  = $this->admin();
        $seller = $this->seller();
        $first  = $this->listing($admin, ['title' => 'Jet-ski Yamaha']);
        $second = $this->listing($admin, ['title' => 'Moteur Suzuki 40cv', 'category' => 'engine', 'type' => null]);
        $third  = $this->listing($seller, ['title' => 'Deja a lui']);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->callTableBulkAction('transfer_bulk', [$first, $second, $third], data: ['new_owner_id' => $seller->id])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('Transfert terminé');

        $this->assertSame($seller->id, $first->refresh()->user_id);
        $this->assertSame($seller->id, $second->refresh()->user_id);
        $this->assertSame($seller->id, $third->refresh()->user_id);
    }

    public function test_the_edit_page_transfers_the_listing_and_shows_the_new_owner(): void
    {
        $admin   = $this->admin();
        $seller  = $this->seller();
        $listing = $this->listing($admin);

        $this->actingAs($admin);

        $page = Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->assertSee('Compte administrateur')
            ->callAction('transfer', data: ['new_owner_id' => $seller->id])
            ->assertHasNoActionErrors()
            ->assertNotified('Annonce transférée');

        $this->assertSame($seller->id, $listing->refresh()->user_id);

        $page->assertSee('Karim Benali')
            ->assertDontSee('Compte administrateur');
    }

    // ── Trouver les annonces « au nom d'Albabor » ────────────────────────────

    public function test_the_list_can_be_filtered_on_admin_owned_listings(): void
    {
        $admin         = $this->admin();
        $seller        = $this->seller();
        $adminListing  = $this->listing($admin, ['title' => 'Publiee par Albabor']);
        $sellerListing = $this->listing($seller, ['title' => 'Publiee par Karim']);

        $this->actingAs($admin);

        Livewire::test(ListListings::class)
            ->assertCanSeeTableRecords([$adminListing, $sellerListing])
            ->filterTable('admin_owned', true)
            ->assertCanSeeTableRecords([$adminListing])
            ->assertCanNotSeeTableRecords([$sellerListing]);
    }

    public function test_the_dashboard_counts_admin_owned_listings(): void
    {
        $admin  = $this->admin();
        $seller = $this->seller();
        $this->listing($admin);
        $this->listing($admin);
        $this->listing($seller);

        $this->actingAs($admin);

        Livewire::test(PendingActionsWidget::class)
            ->assertSee('Annonces au nom d\'un administrateur', escape: false)
            ->assertSee('2');

        $this->assertSame(2, (new PendingActionsWidget)->getAdminOwnedListingsCount());
    }

    // ── Publier au nom d'un vendeur depuis l'administration ──────────────────

    public function test_the_admin_creates_a_listing_on_behalf_of_a_seller(): void
    {
        $admin  = $this->admin();
        $seller = $this->seller();

        $this->actingAs($admin);

        Livewire::test(CreateListing::class)
            ->fillForm([
                'user_id'     => $seller->id,
                'title'       => 'Moteur hors-bord Yamaha 60cv',
                'description' => 'Revise en 2026, tres bon etat.',
                'category'    => 'engine',
                'price_dzd'   => 850_000,
                'currency'    => 'DZD',
                'type_offre'  => 'negociable',
                'etat'        => 'bon_etat',
                'status'      => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $listing = Listing::firstOrFail();
        $this->assertSame($seller->id, $listing->user_id);
        $this->assertSame('active', $listing->status);
    }

    public function test_the_admin_cannot_create_a_listing_on_an_admin_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);

        Livewire::test(CreateListing::class)
            ->fillForm([
                'user_id'     => $admin->id,
                'title'       => 'Bateau au nom d\'Albabor',
                'description' => 'Ne doit plus arriver.',
                'category'    => 'engine',
                'price_dzd'   => 100_000,
                'currency'    => 'DZD',
                'type_offre'  => 'negociable',
                'etat'        => 'bon_etat',
                'status'      => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id']);

        $this->assertSame(0, Listing::count());
    }

    public function test_the_owner_search_never_proposes_an_admin_account(): void
    {
        $this->admin();
        $seller = $this->seller(['email' => 'karim@example.dz']);
        User::factory()->create(['name' => 'Autre Vendeur', 'email' => 'autre@example.dz', 'phone' => '0699000000']);

        $this->assertArrayNotHasKey(User::where('account_type', 'admin')->value('id'), ListingOwnerSelect::search('Albabor'));
        $this->assertSame([], ListingOwnerSelect::search('Admin'));

        $byName  = ListingOwnerSelect::search('karim');
        $byEmail = ListingOwnerSelect::search('karim@example');
        $byPhone = ListingOwnerSelect::search('+213 676 08');

        foreach ([$byName, $byEmail, $byPhone] as $results) {
            $this->assertSame([$seller->id], array_keys($results));
        }

        $this->assertStringContainsString('Karim Benali — karim@example.dz — +213 0676085441', $byName[$seller->id]);
    }

    public function test_the_admin_creates_the_seller_account_on_the_spot(): void
    {
        $service = app(ListingOwnership::class);

        $user = $service->createOwnerAccount([
            'name'         => '  Nadia Cherif ',
            'email'        => 'Nadia.Cherif@Example.DZ',
            'phone'        => '+213 (0)5 55 12 34 56',
            'account_type' => 'vendor',
            'password'     => null,
        ]);

        $this->assertSame('Nadia Cherif', $user->real_name);
        $this->assertSame('nadia.cherif@example.dz', $user->email);
        $this->assertSame('+213', $user->phone_country_code);
        $this->assertSame('0555123456', $user->phone);
        $this->assertSame('vendor', $user->account_type);
        $this->assertTrue($user->canOwnListings());
        $this->assertNotEmpty($user->password, 'Un mot de passe aléatoire est enregistré.');
        $this->assertFalse(Hash::check('', $user->password));

        $withPassword = $service->createOwnerAccount([
            'name'         => 'Yacine',
            'email'        => 'yacine@example.dz',
            'phone'        => '+33612345678',
            'account_type' => 'admin', // jamais depuis ce formulaire
            'password'     => 'motdepasse-choisi',
        ]);

        $this->assertSame('user', $withPassword->account_type);
        $this->assertSame('+33', $withPassword->phone_country_code);
        $this->assertSame('612345678', $withPassword->phone);
        $this->assertTrue(Hash::check('motdepasse-choisi', $withPassword->password));
    }

    // ── Plus jamais d'annonce publiée par le compte administrateur ───────────

    public function test_the_website_sends_an_admin_to_the_panel_instead_of_the_public_form(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('listings.create'))
            ->assertRedirect(route('filament.admin.resources.listings.create'));

        $this->actingAs($admin)
            ->postJson(route('listings.store'), ['title' => 'Au nom d\'Albabor'])
            ->assertStatus(403)
            ->assertJsonPath('message', __('messages.admin_cannot_publish'));

        $this->assertSame(0, Listing::count());
    }

    public function test_the_api_refuses_a_listing_from_an_admin_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/listings', ['title' => 'Au nom d\'Albabor'])
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'Un compte administrateur ne publie pas d\'annonces à son nom. Publiez au nom du vendeur depuis l\'administration (Annonces → Nouvelle annonce).']);

        $this->assertSame(0, Listing::count());
    }

    public function test_the_ownership_rule_is_carried_by_the_user_model(): void
    {
        $this->assertNull($this->seller()->listingOwnershipRefusal());
        $this->assertStringContainsString('administrateur', $this->admin()->listingOwnershipRefusal());
        $this->assertStringContainsString('bloqué', $this->seller(['is_blocked' => true, 'email' => 'b@example.dz'])->listingOwnershipRefusal());
    }
}
