<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListingLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public static function locales(): array
    {
        return [
            'English' => ['en', 'Engine brand', 'Please choose a category.', 'Fresh water (closed circuit)', 'Life raft'],
            'Spanish' => ['es', 'Marca del motor', 'Selecciona una categoría.', 'Agua dulce (circuito cerrado)', 'Balsa salvavidas'],
        ];
    }

    #[DataProvider('locales')]
    public function test_forms_localize_labels_and_client_validation_without_changing_saved_values(
        string $locale, string $engineLabel, string $validationMessage, string $coolingLabel, string $equipmentLabel,
    ): void {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);

        foreach ([route('listings.create'), route('listings.edit', $listing)] as $url) {
            $response = $this->actingAs($seller)->withSession(['locale' => $locale])->get($url)->assertOk();
            $response->assertSee($engineLabel)
                ->assertSee(Js::from($validationMessage)->toHtml(), false)
                ->assertSee($coolingLabel)
                ->assertSee($equipmentLabel)
                ->assertSee('value="Eau douce (circuit fermé)"', false)
                ->assertSee('value="Radeau de survie"', false)
                ->assertDontSee('>Marque du moteur<', false)
                ->assertDontSee("label: 'Categorie'", false)
                ->assertDontSee('name: \'Algerie\'', false);
        }
    }

    #[DataProvider('locales')]
    public function test_detail_translates_catalog_options_and_preserves_the_sellers_own_words(
        string $locale, string $engineLabel, string $validationMessage, string $coolingLabel, string $equipmentLabel,
    ): void {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);

        $this->withSession(['locale' => $locale])->get(route('listings.show', $listing))->assertOk()
            ->assertSee($equipmentLabel)
            ->assertSee($coolingLabel)
            ->assertSee('Mon bateau français')
            ->assertSee('Une description rédigée par le vendeur.')
            ->assertSee('Équipement personnalisé du propriétaire')
            ->assertDontSee('>Radeau de survie<', false);

        $this->assertSame('Eau douce (circuit fermé)', $listing->fresh()->getSpec('motorisation', 'refroidissement'));
        $this->assertSame('Radeau de survie', $listing->fresh()->getSpec('tags', 'equipement')[0]);
    }

    private function listing(User $seller): Listing
    {
        return Listing::create([
            'user_id' => $seller->id,
            'title' => 'Mon bateau français',
            'description' => 'Une description rédigée par le vendeur.',
            'category' => 'boat',
            'type' => 'bateau_peche',
            'price_dzd' => 1_500_000,
            'currency' => 'DZD',
            'etat' => 'bon_etat',
            'status' => 'active',
            'published_until' => now()->addYear(),
            'specs' => [
                'motorisation' => ['refroidissement' => 'Eau douce (circuit fermé)'],
                'tags' => ['equipement' => ['Radeau de survie', 'Équipement personnalisé du propriétaire']],
            ],
        ]);
    }
}
