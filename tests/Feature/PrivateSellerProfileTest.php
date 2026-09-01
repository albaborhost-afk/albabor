<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Profil privé du vendeur : les acheteurs ne voient ni qui a publié l'annonce
 * (« Invité », voir AnonymousSellerNameTest) ni aucune coordonnée directe —
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
}
