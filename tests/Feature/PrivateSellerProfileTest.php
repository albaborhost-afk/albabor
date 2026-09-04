<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Profil privé du vendeur : les acheteurs ne voient ni qui a publié l'annonce
 * (« Privé », voir AnonymousSellerNameTest) ni aucune coordonnée directe —
 * ils ne peuvent le joindre que par la messagerie du site.
 */
class PrivateSellerProfileTest extends TestCase
{
    use RefreshDatabase;

    private const SELLER_PHONE = '0676085441';
    private const MOBILE       = '0555123456';
    private const WHATSAPP     = '0666987654';
    private const EMAIL        = 'karim.prive@example.dz';

    private function seller(bool $private = true, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name'               => 'Karim Benali',
            'hide_name'          => $private,
            'phone'              => self::SELLER_PHONE,
            'phone_country_code' => '+213',
        ], $attributes));
    }

    private function listing(User $seller, array $attributes = []): Listing
    {
        return Listing::create(array_merge([
            'user_id'         => $seller->id,
            'title'           => 'Bateau de peche 6m',
            'description'     => 'En bon etat.',
            'category'        => 'boat',
            'type'            => 'bateau_peche',
            'price_dzd'       => 1_500_000,
            'currency'        => 'DZD',
            'etat'            => 'bon_etat',
            'status'          => 'active',
            'published_until' => now()->addYear(),
            'numero_mobile'   => self::MOBILE,
            'numero_whatsapp' => self::WHATSAPP,
            'contact_email'   => self::EMAIL,
        ], $attributes));
    }

    /** Formulaire de modification complet et valide, tel que le navigateur le poste. */
    private function listingForm(Listing $listing, array $overrides = []): array
    {
        return array_merge([
            'title'       => $listing->title,
            'description' => $listing->description,
            'category'    => $listing->category,
            'type'        => $listing->type,
            'price_dzd'   => $listing->price_dzd,
            'currency'    => $listing->currency,
            'type_offre'  => 'negociable',
            'etat'        => $listing->etat,
        ], $overrides);
    }

    /**
     * Aucune coordonnée du vendeur sur la page. Les liens de la page
     * elle-même (partage WhatsApp de l'annonce, support AlBabor du pied de
     * page) ne sont pas concernés : on cible les numéros du vendeur.
     */
    private function assertNoContactOn($response): void
    {
        $response->assertDontSee(self::MOBILE)
            ->assertDontSee(self::WHATSAPP)
            ->assertDontSee(self::SELLER_PHONE)
            ->assertDontSee(self::EMAIL)
            ->assertDontSee('wa.me/213'.substr(self::WHATSAPP, 1))
            ->assertDontSee('tel:'.self::MOBILE)
            ->assertDontSee('tel:'.self::SELLER_PHONE)
            ->assertDontSee('mailto:'.self::EMAIL);
    }

    // ── La règle ─────────────────────────────────────────────────────────────

    public function test_the_contact_rule_covers_every_viewer(): void
    {
        $private = $this->listing($this->seller(true));
        $public  = $this->listing($this->seller(false, ['email' => 'public@example.dz']));
        $buyer   = User::factory()->create(['phone' => '0555000001']);
        $admin   = User::factory()->create(['account_type' => 'admin', 'phone' => '0550000000']);

        // Profil privé : masqué pour les visiteurs et les autres comptes…
        $this->assertTrue($private->contactHiddenFor(null));
        $this->assertTrue($private->contactHiddenFor($buyer));
        // … jamais pour le vendeur lui-même ni pour l'administration.
        $this->assertFalse($private->contactHiddenFor($private->user));
        $this->assertFalse($private->contactHiddenFor($admin));

        // Profil public : visible, sauf médiation.
        $this->assertFalse($public->contactHiddenFor(null));
        $this->assertFalse($public->contactHiddenFor($buyer));
        $public->mediation_enabled = true;
        $this->assertTrue($public->contactHiddenFor($buyer));
    }

    // ── Site ─────────────────────────────────────────────────────────────────

    public function test_a_visitor_sees_neither_the_name_nor_any_contact_detail(): void
    {
        $this->withoutVite();

        $listing = $this->listing($this->seller());

        $response = $this->get(route('listings.show', $listing))->assertOk();

        $response->assertDontSee('Karim Benali')
            ->assertSeeText(User::ANONYMOUS_NAME)
            ->assertSeeText('ce vendeur ne peut être contacté que par la messagerie')
            ->assertSeeText('Connectez-vous pour envoyer un message');

        $this->assertNoContactOn($response);
    }

    public function test_a_logged_in_buyer_can_only_write_a_message(): void
    {
        $this->withoutVite();

        $listing = $this->listing($this->seller());
        $buyer   = User::factory()->create(['phone' => '0555000001']);

        $response = $this->actingAs($buyer)->get(route('listings.show', $listing))->assertOk();

        $response->assertSee(route('conversations.store', $listing))
            ->assertSeeText('ce vendeur ne peut être contacté que par la messagerie')
            ->assertDontSee('Karim Benali');

        $this->assertNoContactOn($response);
    }

    public function test_a_public_seller_still_shows_the_contact_buttons(): void
    {
        $this->withoutVite();

        $listing = $this->listing($this->seller(false));

        $response = $this->get(route('listings.show', $listing))->assertOk();

        $response->assertSee('Karim Benali')
            ->assertSee('wa.me/')
            ->assertSee('tel:'.self::MOBILE)
            ->assertSee('mailto:'.self::EMAIL)
            ->assertDontSee('ce vendeur ne peut être contacté que par la messagerie');
    }

    public function test_the_admin_still_sees_the_contact_details_of_a_private_seller(): void
    {
        $this->withoutVite();

        $listing = $this->listing($this->seller());
        $admin   = User::factory()->create(['account_type' => 'admin', 'phone' => '0550000000']);

        $this->actingAs($admin)
            ->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee('Karim Benali')
            ->assertSee('tel:'.self::MOBILE);
    }

    public function test_the_seller_is_reminded_of_the_private_profile_on_their_own_listing(): void
    {
        $this->withoutVite();

        $seller  = $this->seller();
        $listing = $this->listing($seller);

        $this->actingAs($seller)
            ->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSeeText('ne voient ni votre numéro ni votre e-mail');
    }

    // ── API (applications mobiles) ───────────────────────────────────────────

    public function test_the_api_hides_the_contact_details_of_a_private_seller(): void
    {
        $listing = $this->listing($this->seller());

        $response = $this->getJson('/api/v1/listings/'.$listing->id)->assertOk();

        $response->assertJsonPath('listing.user.name', User::ANONYMOUS_NAME)
            ->assertJsonMissingPath('listing.numero_mobile')
            ->assertJsonMissingPath('listing.numero_whatsapp')
            ->assertJsonMissingPath('listing.contact_email')
            ->assertJsonMissingPath('listing.user.phone');

        $this->assertStringNotContainsString(self::MOBILE, $response->getContent());
        $this->assertStringNotContainsString(self::SELLER_PHONE, $response->getContent());
        $this->assertStringNotContainsString('Karim Benali', $response->getContent());
    }

    public function test_the_api_listing_feed_hides_them_too(): void
    {
        $this->listing($this->seller());

        $response = $this->getJson('/api/v1/listings')->assertOk();

        $this->assertStringNotContainsString(self::MOBILE, $response->getContent());
        $this->assertStringNotContainsString(self::WHATSAPP, $response->getContent());
        $this->assertStringNotContainsString(self::SELLER_PHONE, $response->getContent());
        $this->assertStringNotContainsString(self::EMAIL, $response->getContent());
    }

    public function test_the_api_still_exposes_the_contact_details_of_a_public_seller(): void
    {
        $listing = $this->listing($this->seller(false));

        $this->getJson('/api/v1/listings/'.$listing->id)
            ->assertOk()
            ->assertJsonPath('listing.numero_mobile', self::MOBILE)
            ->assertJsonPath('listing.numero_whatsapp', self::WHATSAPP)
            ->assertJsonPath('listing.contact_email', self::EMAIL)
            ->assertJsonPath('listing.user.phone', self::SELLER_PHONE);
    }

    public function test_the_seller_sees_their_own_contact_details_through_the_api(): void
    {
        $seller  = $this->seller();
        $listing = $this->listing($seller);

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/listings/'.$listing->id)
            ->assertOk()
            ->assertJsonPath('listing.numero_mobile', self::MOBILE)
            ->assertJsonPath('listing.user.phone', self::SELLER_PHONE)
            ->assertJsonPath('listing.user.hide_name', true);
    }

    // ── Réglage par le vendeur ───────────────────────────────────────────────

    public function test_the_seller_switches_the_private_profile_on_from_their_profile(): void
    {
        $seller  = $this->seller(false);
        $listing = $this->listing($seller);

        $this->assertFalse($listing->contactHiddenFor(null));

        $this->actingAs($seller)->put(route('profile.update'), [
            'name'      => 'Karim Benali',
            'phone'     => self::SELLER_PHONE,
            'hide_name' => 1,
        ])->assertRedirect();

        $this->assertTrue($seller->fresh()->hasPrivateProfile());
        $this->assertTrue($listing->fresh()->contactHiddenFor(null));
    }

    public function test_the_profile_page_explains_the_private_profile(): void
    {
        $this->withoutVite();

        $this->actingAs($this->seller())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Profil privé')
            ->assertSeeText('uniquement par la messagerie');
    }

    // ── Pas de profil public ─────────────────────────────────────────────────

    public function test_the_listing_page_no_longer_links_to_the_seller_page(): void
    {
        $this->withoutVite();
        $seller = $this->seller();
        $buyer  = User::factory()->create();

        // Deux annonces : la vue d'une même annonce deux fois le même jour est
        // un cas à part sur SQLite (ListingView::recordView).
        $this->actingAs($buyer)->get(route('listings.show', $this->listing($seller)))
            ->assertOk()
            ->assertDontSee(route('sellers.show', $seller))
            ->assertDontSeeText('Voir toutes ses annonces')
            ->assertSeeText('Contact par messagerie uniquement');

        // Le vendeur, lui, garde le lien vers sa propre page.
        $this->actingAs($seller)->get(route('listings.show', $this->listing($seller)))
            ->assertOk()
            ->assertSee(route('sellers.show', $seller));
    }

    public function test_a_private_seller_has_no_public_page(): void
    {
        $this->withoutVite();
        $seller = $this->seller();
        $this->listing($seller);
        $buyer  = User::factory()->create();
        $admin  = User::factory()->create(['account_type' => 'admin']);

        $this->get(route('sellers.show', $seller))->assertNotFound();
        $this->actingAs($buyer)->get(route('sellers.show', $seller))->assertNotFound();

        // Le vendeur et l'administration la voient encore, avec le rappel.
        $this->actingAs($seller)->get(route('sellers.show', $seller))
            ->assertOk()
            ->assertSeeText('Karim Benali')
            ->assertSeeText('visible que par vous');
        $this->actingAs($admin)->get(route('sellers.show', $seller))->assertOk();

        // Un profil public garde sa page.
        $public = $this->seller(false, ['email' => 'public@example.dz']);
        $this->listing($public);
        $this->get(route('sellers.show', $public))->assertOk();
    }

    public function test_the_api_has_no_vendor_page_for_a_private_seller(): void
    {
        $seller = $this->seller();
        $this->listing($seller);
        $buyer  = User::factory()->create();

        $this->getJson('/api/v1/vendors/'.$seller->id)
            ->assertNotFound()
            ->assertJsonPath('message', __('messages.private_seller_no_public_page'))
            ->assertJsonPath('code', 'private_profile');
        $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/vendors/'.$seller->id)->assertNotFound();

        $this->actingAs($seller, 'sanctum')->getJson('/api/v1/vendors/'.$seller->id)
            ->assertOk()
            ->assertJsonPath('user.name', 'Karim Benali')
            ->assertJsonPath('user.hide_name', true);
    }

    // ── Aucun autre écran ne livre le numéro ─────────────────────────────────

    public function test_the_conversation_api_does_not_reveal_the_private_sellers_phone(): void
    {
        $seller  = $this->seller();
        $listing = $this->listing($seller);
        $buyer   = User::factory()->create(['phone' => '0555000001']);

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/v1/conversations/listing/'.$listing->id, ['body' => 'Bonjour'])
            ->assertCreated()
            ->assertJsonPath('conversation.seller.name', User::ANONYMOUS_NAME)
            ->assertJsonPath('conversation.seller.hide_name', true)
            ->assertJsonMissingPath('conversation.seller.phone')
            ->assertJsonMissingPath('conversation.listing.numero_mobile')
            ->assertJsonMissingPath('conversation.listing.numero_whatsapp');

        $conversation = Conversation::firstOrFail();

        foreach (['/api/v1/conversations', '/api/v1/conversations/'.$conversation->id] as $url) {
            $body = $this->actingAs($buyer, 'sanctum')->getJson($url)->assertOk()->getContent();
            $this->assertStringNotContainsString(self::SELLER_PHONE, $body, $url);
            $this->assertStringNotContainsString(self::MOBILE, $body, $url);
            $this->assertStringNotContainsString(self::WHATSAPP, $body, $url);
            $this->assertStringNotContainsString('Karim Benali', $body, $url);
        }

        // Le vendeur voit son propre numéro et le nom de l'acheteur.
        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/conversations/'.$conversation->id)
            ->assertOk()
            ->assertJsonPath('seller.phone', self::SELLER_PHONE)
            ->assertJsonPath('buyer.name', $buyer->name);
    }

    public function test_the_favorites_api_hides_the_contact_details_of_a_private_seller(): void
    {
        $seller  = $this->seller();
        $listing = $this->listing($seller);
        $buyer   = User::factory()->create();
        Favorite::create(['user_id' => $buyer->id, 'listing_id' => $listing->id]);

        $body = $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/favorites')->assertOk()->getContent();

        $this->assertStringContainsString($listing->title, $body);
        $this->assertStringNotContainsString(self::MOBILE, $body);
        $this->assertStringNotContainsString(self::WHATSAPP, $body);
        $this->assertStringNotContainsString(self::SELLER_PHONE, $body);
        $this->assertStringNotContainsString(self::EMAIL, $body);
        $this->assertStringNotContainsString('Karim Benali', $body);
    }

    public function test_the_favorites_api_still_shows_the_contact_details_of_a_public_seller(): void
    {
        $listing = $this->listing($this->seller(false, ['email' => 'public@example.dz']));
        $buyer   = User::factory()->create();
        Favorite::create(['user_id' => $buyer->id, 'listing_id' => $listing->id]);

        $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonPath('data.0.numero_mobile', self::MOBILE);
    }

    // ── « Publier anonymement » depuis le formulaire d'annonce ──────────────

    public function test_the_listing_forms_offer_anonymous_publishing(): void
    {
        $this->withoutVite();
        $seller  = $this->seller(false);
        $listing = $this->listing($seller);

        $this->actingAs($seller)->get(route('listings.create'))
            ->assertOk()->assertSeeText('Publier anonymement');
        $this->actingAs($seller)->get(route('listings.edit', $listing))
            ->assertOk()->assertSeeText('Publier anonymement');
    }

    public function test_saving_the_listing_with_anonymous_publishing_makes_the_profile_private(): void
    {
        $seller  = $this->seller(false);
        $listing = $this->listing($seller);

        $this->actingAs($seller)
            ->put(route('listings.update', $listing), $this->listingForm($listing, ['hide_name' => 1]))
            ->assertSessionHasNoErrors();

        $this->assertTrue($seller->fresh()->hasPrivateProfile());
        $this->assertTrue($listing->fresh()->contactHiddenFor(null));

        // Et retour, depuis le même formulaire.
        $this->actingAs($seller)
            ->put(route('listings.update', $listing), $this->listingForm($listing, ['hide_name' => 0]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($seller->fresh()->hasPrivateProfile());
    }

    public function test_an_admin_editing_the_listing_does_not_flip_any_profile(): void
    {
        $seller  = $this->seller(false);
        $listing = $this->listing($seller);
        $admin   = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($admin)
            ->put(route('listings.update', $listing), $this->listingForm($listing, ['hide_name' => 1]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($admin->fresh()->hasPrivateProfile());
        $this->assertFalse($seller->fresh()->hasPrivateProfile());
    }
}
