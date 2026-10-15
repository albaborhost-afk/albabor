<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public static function languages(): array
    {
        return [['fr'], ['en'], ['es']];
    }

    #[DataProvider('languages')]
    public function test_switching_language_persists_across_public_and_account_pages(string $locale): void
    {
        $this->withoutVite();
        $seller = User::factory()->create(['phone' => '+213555123456']);
        $listing = Listing::create([
            'user_id' => $seller->id, 'title' => 'Texte original du vendeur',
            'description' => 'Description personnelle non traduite', 'category' => 'boat',
            'type' => 'bateau_peche', 'price_dzd' => 1500000, 'currency' => 'DZD',
            'etat' => 'bon_etat', 'type_offre' => 'negociable', 'status' => 'active',
            'published_until' => now()->addYear(),
        ]);

        $this->from(route('home'))->get(route('lang.switch', $locale))
            ->assertRedirect(route('home'))->assertSessionHas('locale', $locale)
            ->assertCookie('locale', $locale);

        foreach (['home', 'listings.index', 'login', 'register', 'publicite.create', 'boutiques.index', 'vendor.landing'] as $page) {
            $this->get(route($page))->assertOk()->assertSee('lang="'.$locale.'"', false)
                ->assertDontSee('messages.', false);
        }

        $this->actingAs($seller);
        foreach (['profile.show', 'profile.edit', 'listings.create', 'listings.my', 'favorites.index', 'conversations.index', 'payments.index', 'mediation.index', 'subscription.plans'] as $page) {
            $this->get(route($page))->assertOk()->assertSee('lang="'.$locale.'"', false)
                ->assertDontSee('messages.', false);
        }
        $this->get(route('listings.edit', $listing))->assertOk()->assertSee('lang="'.$locale.'"', false);
        $this->get(route('listings.show', $listing))->assertOk()
            ->assertSeeText('Texte original du vendeur')->assertSeeText('Description personnelle non traduite');
        $this->assertSame('bateau_peche', $listing->fresh()->type);
        $this->assertSame('bon_etat', $listing->fresh()->etat);
    }

    public function test_language_choice_survives_a_new_session(): void
    {
        $this->withoutVite();
        $this->withCookie('locale', 'es')->get(route('home'))->assertOk()->assertSee('lang="es"', false);
    }

    public function test_invalid_language_does_not_replace_the_selected_language(): void
    {
        $this->withSession(['locale' => 'en'])->get(route('lang.switch', 'invalid'))->assertRedirect()
            ->assertSessionHas('locale', 'en');
    }

    public function test_api_language_headers_translate_validation_and_display_labels(): void
    {
        $seller = User::factory()->create(['hide_name' => true]);
        $listing = Listing::create([
            'user_id' => $seller->id, 'title' => 'Bateau personnel', 'description' => 'Description originale',
            'category' => 'boat', 'type' => 'bateau_peche', 'price_dzd' => 100,
            'currency' => 'DZD', 'etat' => 'bon_etat', 'status' => 'active',
        ]);
        foreach ([['en-US,en;q=0.9', 'Private'], ['es-ES,es;q=0.9', 'Privado'], ['fr-FR', 'Privé']] as [$header, $name]) {
            $this->withHeader('Accept-Language', $header)->getJson('/api/v1/listings/'.$listing->id)
                ->assertOk()->assertJsonPath('listing.user.name', $name)
                ->assertJsonPath('listing.title', 'Bateau personnel')
                ->assertJsonPath('listing.etat', 'bon_etat');
        }
        $this->withHeader('Accept-Language', 'es')->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'El campo correo electrónico es obligatorio.');
    }

    public function test_api_without_a_language_preference_defaults_to_french(): void
    {
        $this->withHeader('Accept-Language', '')->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Le champ adresse e-mail est obligatoire.');
    }

    public function test_filters_translate_labels_but_keep_original_query_values(): void
    {
        $this->withoutVite();
        $response = $this->withSession(['locale' => 'es'])->get(route('listings.index', [
            'type' => 'bateau_peche', 'currency' => 'OTHER', 'pays' => 'Algérie',
        ]))->assertOk();
        $response->assertSeeText('Tipo de oferta')->assertSeeText('Argelia')
            ->assertSeeText('Barcos de pesca')->assertSeeText('Otro')
            ->assertSee('value="Algérie" selected', false)
            ->assertSee('value="bateau_peche" selected', false)
            ->assertSee('value="OTHER" selected', false);
    }

    public function test_cacheable_api_responses_vary_by_language(): void
    {
        $response = $this->withHeader('Accept-Language', 'es')->getJson('/api/v1/banners')->assertOk();
        $this->assertStringContainsString('Accept-Language', $response->headers->get('Vary'));
        $response->assertHeader('Cache-Control', 'max-age=300, public');
    }

    public function test_price_unit_plural_matches_the_displayed_rounded_amount(): void
    {
        app()->setLocale('es');
        $listing = new Listing(['currency' => 'DZD', 'price_dzd' => 10001, 'price_display_unit' => 'million']);
        $this->assertSame('1 Millón', $listing->formatted_price);
        $listing->price_dzd = 19999;
        $this->assertSame('2 Millones', $listing->formatted_price);
        app()->setLocale('fr');
        $this->assertSame('2 Millions', $listing->formatted_price);
    }
}
