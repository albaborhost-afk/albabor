<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use App\Support\ListingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Les caractéristiques saisies par le vendeur doivent survivre à l'enregistrement.
 *
 * `validated()` supprime les clés d'un tableau validé qui n'ont pas de règle
 * propre : seules 7 des 42 caractéristiques en avaient, les autres étaient
 * jetées en silence et la fiche n'affichait qu'une seule vignette.
 */
class ListingSpecsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.listing_disk', 'public'));
    }

    /** Une valeur pour chaque caractéristique déclarée au catalogue. */
    private function fullSpecs(): array
    {
        $values = [
            'general' => ['modele' => 'FX Cruiser SHO', 'fabricant' => 'Yamaha',
                'annee_construction' => '2022', 'immatriculation' => 'Autre',
                'immatriculation_autre' => 'Portugal', 'nombre_places' => '3',
                'part_number' => 'REF-8891', 'part_type' => 'Hélice', 'compatible_with' => 'Yamaha FX'],
            'dimensions' => ['longueur' => '3.6', 'largeur' => '1.2', 'tirant_eau' => '0.4',
                'tirant_air' => '1.1', 'tonnage' => '480', 'tonnage_t' => '0.48', 'tonnage_unit' => 'kg'],
            'motorisation' => ['marque_moteur' => 'Yamaha', 'propulsion' => 'Jet',
                'type_carburant' => 'Essence', 'type_helice' => 'Jet moteur', 'nombre_moteurs' => '1',
                'puissance_par_moteur' => '250', 'puissance_totale' => '250', 'nombre_heures' => '45',
                'cylindree' => '1812', 'nombre_cylindres' => '4', 'refroidissement' => 'Eau de mer'],
            'reservoirs' => ['nombre_reservoirs' => '1', 'reservoir_carburant' => '70',
                'reservoir_eau_douce' => '10', 'stockage' => '5'],
            'amenagements' => ['nombre_cabines' => '1', 'nombre_couchettes' => '2',
                'nombre_cuisine' => '1', 'nombre_sanitaire' => '1'],
            'extras' => ['remorque' => 'Oui', 'marque_remorque' => 'Satellite', 'place_au_port' => 'Oui',
                'adresse_port' => 'Port de Bouharoun', 'longueur_place' => '8',
                'largeur_place' => '3', 'annexe' => 'Oui'],
        ];

        // Le test échoue si une caractéristique du catalogue n'est pas couverte.
        foreach (ListingCatalog::SPEC_FIELDS as $section => $fields) {
            $this->assertSame([], array_diff($fields, array_keys($values[$section] ?? [])),
                "Caractéristiques non couvertes par le test dans « {$section} »");
        }

        $values['tags'] = ['equipement' => ['Extincteurs'], 'options' => ['Bimini'],
            'electronique' => ['Radio VHF'], 'extras' => ['Annexe']];

        return $values;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'YAMAHA FX CRUISER SHO',
            'description' => 'En tres bon etat, avec remorque.',
            'category' => 'jetski',
            'type' => 'jetski_standard',
            'price_dzd' => 36_500_000,
            'currency' => 'DZD',
            'type_offre' => 'negociable',
            'etat' => 'bon_etat',
            'wilaya' => 'Alger',
            'specs' => $this->fullSpecs(),
            'images' => [UploadedFile::fake()->image('photo.jpg', 800, 600)],
        ], $overrides);
    }

    private function assertAllSpecsStored(Listing $listing): void
    {
        $specs = $listing->fresh()->specs;
        $expected = $this->fullSpecs();

        foreach (ListingCatalog::SPEC_FIELDS as $section => $fields) {
            foreach ($fields as $field) {
                $this->assertArrayHasKey($section, $specs, "Section « {$section} » perdue");
                $this->assertArrayHasKey($field, $specs[$section],
                    "Caractéristique « {$section}.{$field} » perdue à l'enregistrement");
                $this->assertEquals($expected[$section][$field], $specs[$section][$field],
                    "Caractéristique « {$section}.{$field} » altérée");
            }
        }

        $this->assertSame(['Extincteurs'], $specs['tags']['equipement']);
    }

    private function seller(): User
    {
        return User::factory()->create(['phone' => '+213670000000']);
    }

    public function test_the_website_keeps_every_specification_on_create(): void
    {
        $user = $this->seller();

        $this->actingAs($user)->postJson(route('listings.store'), $this->payload())->assertOk();

        $this->assertAllSpecsStored(Listing::where('user_id', $user->id)->sole());
    }

    public function test_the_website_keeps_every_specification_on_update(): void
    {
        $user = $this->seller();
        $this->actingAs($user)->postJson(route('listings.store'), $this->payload(['specs' => null]))->assertOk();
        $listing = Listing::where('user_id', $user->id)->sole();

        $this->actingAs($user)
            ->putJson(route('listings.update', $listing), $this->payload(['images' => null]))
            ->assertSuccessful();

        $this->assertAllSpecsStored($listing);
    }

    public function test_the_api_keeps_every_specification_on_create(): void
    {
        Sanctum::actingAs($user = $this->seller());

        $this->postJson('/api/v1/listings', $this->payload())->assertSuccessful();

        $this->assertAllSpecsStored(Listing::where('user_id', $user->id)->sole());
    }

    public function test_the_api_keeps_every_specification_on_update(): void
    {
        Sanctum::actingAs($user = $this->seller());
        $this->postJson('/api/v1/listings', $this->payload(['specs' => null]))->assertSuccessful();
        $listing = Listing::where('user_id', $user->id)->sole();

        $this->putJson("/api/v1/listings/{$listing->id}", $this->payload(['images' => null]))
            ->assertSuccessful();

        $this->assertAllSpecsStored($listing);
    }

    public function test_empty_tanks_do_not_produce_an_empty_reservoirs_card(): void
    {
        $specs = $this->fullSpecs();
        $specs['reservoirs'] = ['nombre_reservoirs' => '', 'reservoir_carburant' => '',
            'reservoir_eau_douce' => '', 'stockage' => ''];

        $user = $this->seller();
        $this->actingAs($user)->postJson(route('listings.store'), $this->payload(['specs' => $specs]))->assertOk();

        $listing = Listing::where('user_id', $user->id)->sole();

        $this->assertFalse($listing->hasSpecSection('reservoirs'),
            'Une section Reservoirs vide ne doit pas être affichée');
        $this->assertNull($listing->getSpec('reservoirs', 'total_carburant'));
    }
}
