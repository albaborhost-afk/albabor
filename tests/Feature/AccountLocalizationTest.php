<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Plan;
use App\Models\VendorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_authentication_pages_keep_the_selected_language_and_translate_country_names(): void
    {
        foreach (['en' => ['Register', 'Germany'], 'es' => ['Registrarse', 'Alemania']] as $locale => [$register, $country]) {
            $this->withSession(['locale' => $locale]);
            $this->get(route('register'))
                ->assertOk()
                ->assertSee('lang="'.$locale.'"', false)
                ->assertSee($country)
                ->assertDontSee("name: 'Allemagne'", false);

            foreach (['login', 'password.request'] as $route) {
                $this->get(route($route))->assertOk()->assertSee('lang="'.$locale.'"', false);
            }
        }
    }

    public function test_legal_and_seller_pages_translate_the_body_and_not_only_the_navigation(): void
    {
        $pages = [
            'pages.privacy' => ['Data we collect', 'Datos que recopilamos'],
            'pages.terms' => ['Posting listings', 'Publicación de anuncios'],
            'pages.legal' => ['Checks before buying', 'Comprobaciones antes de comprar'],
            'pages.sale-authorization' => ['Owner information', 'Información del propietario'],
            'vendor.landing' => ['Everything you need to sell', 'Todo lo que necesitas para vender'],
            'boutiques.index' => ['No shops yet', 'Todavía no hay tiendas'],
        ];

        foreach (['en', 'es'] as $index => $locale) {
            $this->withSession(['locale' => $locale]);
            foreach ($pages as $route => $expected) {
                $this->get(route($route))->assertOk()->assertSee($expected[$index]);
            }
        }
    }

    public function test_account_and_shop_forms_translate_labels_and_preserve_user_information(): void
    {
        $user = User::factory()->create(['name' => 'François Martin', 'phone' => '+213670000010']);
        $this->actingAs($user);

        foreach (['en' => ['Total listings', 'Create your shop', 'Current password'], 'es' => ['Total de anuncios', 'Crea tu tienda', 'Contraseña actual']] as $locale => [$stats, $shop, $password]) {
            $this->withSession(['locale' => $locale]);
            $this->get(route('profile.show'))->assertOk()->assertSee($stats)->assertSee('François Martin');
            $this->get(route('profile.edit'))->assertOk()->assertSee($password)->assertSee('François');
            $this->get(route('vendor.onboarding.create'))->assertOk()->assertSee($shop)->assertDontSee('PNG ou JPG');
            $this->get(route('favorites.index'))->assertOk();
            $this->get(route('payments.index'))->assertOk()->assertDontSee('Historique de vos transactions');
            $this->get(route('mediation.index'))->assertOk()->assertDontSee('Gerez vos demandes de mediation');
        }
    }

    public function test_subscription_features_are_translated_and_keep_their_emphasis(): void
    {
        $user = User::factory()->create();
        Plan::create([
            'name' => 'Pro',
            'price_dzd' => 5000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        foreach (['en' => 'Unlimited listings', 'es' => 'Anuncios ilimitados'] as $locale => $feature) {
            $this->actingAs($user)->withSession(['locale' => $locale])
                ->get(route('subscription.plans'))
                ->assertOk()
                ->assertSee('<strong>'.$feature.'</strong>', false)
                ->assertDontSee('Annonces illimitees');
        }
    }

    public function test_shop_and_seller_listing_counts_use_translated_plurals(): void
    {
        $seller = User::factory()->create(['name' => 'François Marine', 'account_type' => 'vendor']);
        $shop = VendorProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Pièces Marine Oran',
            'is_active' => true,
        ]);
        for ($i = 0; $i < 2; $i++) {
            Listing::create([
                'user_id' => $seller->id,
                'title' => 'Moteur Yamaha '.$i,
                'description' => 'Description originale en français.',
                'category' => 'engine',
                'price_dzd' => 50000,
                'wilaya' => 'Oran',
                'status' => 'active',
                'published_until' => now()->addYear(),
            ]);
        }

        foreach (['en' => ['2 listings', '1 shop available'], 'es' => ['2 anuncios', '1 tienda disponible']] as $locale => [$listings, $shops]) {
            $this->withSession(['locale' => $locale]);
            $this->get(route('boutiques.index'))->assertOk()->assertSee($shops)->assertSee($listings);
            $this->get(route('boutiques.show', $shop))->assertOk()->assertSee($listings)->assertSee('Pièces Marine Oran');
            $this->get(route('sellers.show', $seller))->assertOk()->assertSee($listings)->assertSee('François Marine');
        }
    }

}
