<?php

namespace Tests\Feature;

use App\Filament\Resources\ListingResource\Pages\CreateListing;
use App\Filament\Resources\ListingResource\Pages\EditListing;
use App\Filament\Vendor\Resources\VendorListingResource\Pages\EditVendorListing;
use App\Models\Listing;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminListingPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_replace([
            'user_id' => User::factory()->create()->id,
            'title' => 'Bateau complet', 'description' => 'Description conservée',
            'category' => 'boat', 'type' => 'yacht', 'etat' => 'bon_etat',
            'price_dzd' => 19500000, 'currency' => 'DZD', 'type_offre' => 'negociable',
            'status' => 'active', 'pays' => 'Algérie', 'wilaya' => 'Alger',
            'numero_whatsapp' => '+213555123456', 'numero_mobile' => '+213555987654',
            'contact_email' => 'seller@example.test',
            'specs' => [
                'general' => ['modele' => 'Flyer 6', 'fabricant' => 'Bénéteau',
                    'annee_construction' => '2024', 'immatriculation' => 'Autre',
                    'immatriculation_autre' => 'Portugal'],
                'dimensions' => ['longueur' => '6.5', 'largeur' => '2.5', 'tirant_eau' => '0.8',
                    'tirant_air' => '2.7', 'tonnage' => '1800', 'tonnage_unit' => 'kg'],
                'motorisation' => ['marque_moteur' => 'Yamaha', 'propulsion' => 'Hors-Bord',
                    'type_carburant' => 'Essence', 'nombre_moteurs' => '2',
                    'puissance_par_moteur' => '150', 'nombre_heures' => '120', 'type_helice' => 'Embase'],
                'reservoirs' => ['nombre_reservoirs' => '2', 'reservoir_carburant' => '150',
                    'reservoir_eau_douce' => '100', 'stockage' => '20'],
                'amenagements' => ['nombre_couchettes' => '2', 'nombre_cabines' => '1',
                    'nombre_sanitaire' => '1', 'nombre_cuisine' => '0'],
                'tags' => ['equipement' => ['Gilets de sauvetage', 'Pompe de cale'],
                    'options' => ['Bimini', 'Table cockpit'], 'electronique' => ['GPS / Traceur de cartes', 'Sondeur'],
                    'extras' => ['Annexe', 'Équipement personnel, sur mesure']],
                'extras' => ['annexe' => true, 'remorque' => true, 'marque_remorque' => 'Satellite',
                    'place_au_port' => true, 'longueur_place' => '8', 'largeur_place' => '3', 'adresse_port' => 'Port de test'],
                'additional_specs' => ['builder_note' => 'Valeur non représentée dans le formulaire'],
            ],
        ], $attributes));
    }

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['account_type' => 'admin']));
    }

    public function test_saving_only_the_price_preserves_every_existing_specification_and_photo(): void
    {
        $this->admin();
        $listing = $this->listing();
        $media = $listing->media()->create(['path' => 'listings/test.jpg', 'order' => 0]);
        $before = $listing->fresh()->specs;
        $contacts = $listing->only(['numero_whatsapp', 'numero_mobile', 'contact_email']);

        for ($save = 0; $save < 3; $save++) {
            Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
                ->fillForm(['price_dzd' => 20000000 + $save])
                ->call('save')->assertHasNoFormErrors();
            $this->assertEquals($before, $listing->fresh()->specs);
        }

        $saved = $listing->fresh();
        $this->assertEquals(20000002, $saved->price_dzd);
        $this->assertEquals($before, $saved->specs);
        $this->assertSame($contacts, $saved->only(array_keys($contacts)));
        $this->assertSame([$media->id], $saved->media()->pluck('id')->all());
    }

    public function test_hidden_sections_survive_edits_for_every_listing_category(): void
    {
        $this->admin();

        foreach (['boat' => 'yacht', 'jetski' => 'jetski_standard', 'engine' => null, 'parts' => null] as $category => $type) {
            $listing = $this->listing(compact('category', 'type'));
            $specs = $listing->specs;
            $specs['general'] += ['part_number' => 'REF-42', 'compatible_with' => 'Yamaha', 'part_type' => 'mechanical'];
            $listing->update(['specs' => $specs]);
            $before = $listing->fresh()->specs;

            Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
                ->fillForm(['title' => 'Titre modifié '.$category])
                ->call('save')->assertHasNoFormErrors();

            $this->assertSame('Titre modifié '.$category, $listing->fresh()->title);
            $this->assertEquals($before, $listing->fresh()->specs, $category);
        }
    }

    public function test_intentional_clearing_and_tag_removal_do_not_restore_old_values(): void
    {
        $this->admin();
        $listing = $this->listing();
        $before = $listing->specs;

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm([
                'specs.general.annee_construction' => null,
                'specs.general.fabricant' => null,
                'specs.reservoirs.stockage' => 0,
                'specs.tags.equipement' => [],
                'specs.tags.options' => ['Bimini'],
                'specs.tags.extras' => [],
                'specs.extras.annexe' => false,
                'specs.extras.remorque' => false,
                'specs.extras.place_au_port' => false,
                'contact_email' => null,
            ])
            ->call('save')->assertHasNoFormErrors();

        // Reopening and saving again must not bring deleted values back.
        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->call('save')->assertHasNoFormErrors();

        $saved = $listing->fresh();
        $this->assertNull($saved->getSpec('general', 'annee_construction'));
        $this->assertNull($saved->getSpec('general', 'fabricant'));
        $this->assertEquals(0, $saved->getSpec('reservoirs', 'stockage'));
        $this->assertEquals(400, $saved->getSpec('reservoirs', 'capacite_totale'));
        $this->assertSame([], $saved->getSpec('tags', 'equipement'));
        $this->assertSame(['Bimini'], $saved->getSpec('tags', 'options'));
        $this->assertSame([], $saved->getSpec('tags', 'extras'));
        $this->assertFalse($saved->getSpec('extras', 'annexe'));
        $this->assertFalse($saved->getSpec('extras', 'remorque'));
        $this->assertFalse($saved->getSpec('extras', 'place_au_port'));
        $this->assertSame('Satellite', $saved->getSpec('extras', 'marque_remorque'));
        $this->assertNull($saved->contact_email);
        $this->assertSame($before['additional_specs'], $saved->specs['additional_specs']);
    }

    public function test_admin_can_edit_new_specs_and_public_site_and_api_show_saved_values(): void
    {
        $this->admin();
        $listing = $this->listing();

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm([
                'specs.general.immatriculation_autre' => 'Pays de test',
                'specs.motorisation.type_helice' => 'IPS 360°',
                'specs.motorisation.nombre_moteurs' => 3,
                'specs.motorisation.puissance_par_moteur' => 100,
                'specs.reservoirs.nombre_reservoirs' => 3,
                'specs.reservoirs.reservoir_carburant' => 200,
                'specs.tags.extras' => ['Équipement personnel, sur mesure'],
            ])
            ->call('save')->assertHasNoFormErrors();

        $saved = $listing->fresh();
        $this->assertSame('Pays de test', $saved->getSpec('general', 'immatriculation_autre'));
        $this->assertSame('IPS 360°', $saved->getSpec('motorisation', 'type_helice'));
        $this->assertEquals(300, $saved->getSpec('motorisation', 'puissance_totale'));
        $this->assertEquals(600, $saved->getSpec('reservoirs', 'total_carburant'));
        $this->assertEquals(720, $saved->getSpec('reservoirs', 'capacite_totale'));
        $this->assertSame(['Équipement personnel, sur mesure'], $saved->getSpec('tags', 'extras'));

        $this->getJson('/api/v1/listings/'.$listing->id)->assertOk()
            ->assertJsonPath('listing.specs.general.annee_construction', '2024')
            ->assertJsonPath('listing.specs.motorisation.puissance_totale', 300)
            ->assertJsonPath('listing.specs.reservoirs.capacite_totale', 720)
            ->assertJsonPath('listing.specs.tags.extras', ['Équipement personnel, sur mesure']);
        $this->get('/annonces/'.$listing->id)->assertOk()
            ->assertSee('2024')->assertSee('720')->assertSee('Équipement personnel, sur mesure');
    }

    public function test_legacy_comma_separated_equipment_is_retained_as_lists(): void
    {
        $this->admin();
        $listing = $this->listing();
        $specs = $listing->specs;
        $specs['tags'] = ['equipement' => 'Gilets de sauvetage,Pompe de cale',
            'options' => 'Bimini, Table cockpit', 'electronique' => 'GPS,Sondeur', 'extras' => 'Annexe'];
        DB::table('listings')->where('id', $listing->id)->update(['specs' => json_encode($specs)]);

        $form = Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()]);
        $form->assertFormSet(['specs.tags.options' => ['Bimini', 'Table cockpit']])
            ->fillForm(['title' => 'Ancienne annonce modifiée'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame(['Gilets de sauvetage', 'Pompe de cale'], $listing->fresh()->getSpec('tags', 'equipement'));
        $this->assertSame(['Bimini', 'Table cockpit'], $listing->fresh()->getSpec('tags', 'options'));
        $this->assertSame(['GPS', 'Sondeur'], $listing->fresh()->getSpec('tags', 'electronique'));
        $this->assertSame(['Annexe'], $listing->fresh()->getSpec('tags', 'extras'));
    }

    public function test_invalid_specification_prevents_any_listing_changes(): void
    {
        $this->admin();
        $listing = $this->listing();
        $before = $listing->fresh()->getRawOriginal();

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['title' => 'Ne doit pas être enregistré', 'specs.reservoirs.nombre_reservoirs' => -1])
            ->call('save')->assertHasFormErrors(['specs.reservoirs.nombre_reservoirs']);

        $this->assertSame($before, $listing->fresh()->getRawOriginal());
    }

    public function test_listing_without_optional_specs_can_still_be_saved(): void
    {
        $this->admin();
        $listing = $this->listing(['specs' => null]);

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['price_dzd' => 100])
            ->call('save')->assertHasNoFormErrors();

        $this->assertEquals(100, $listing->fresh()->price_dzd);
        $this->assertNull($listing->fresh()->getSpec('general', 'annee_construction'));
        $this->assertNull($listing->fresh()->getSpec('motorisation', 'puissance_totale'));
    }

    public function test_vendor_edits_leave_unrepresented_specs_intact(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('vendeur'));
        $vendor = User::factory()->create(['account_type' => 'vendor']);
        $this->actingAs($vendor);

        foreach (['engine', 'parts'] as $category) {
            $listing = $this->listing(['category' => $category, 'type' => null, 'user_id' => $vendor->id]);
            $before = $listing->fresh()->specs;
            Livewire::test(EditVendorListing::class, ['record' => $listing->getRouteKey()])
                ->fillForm(['price_dzd' => 20000])->call('save')->assertHasNoFormErrors();

            $this->assertEquals(20000, $listing->fresh()->price_dzd);
            $this->assertEquals($before, $listing->fresh()->specs);
        }
    }

    public function test_admin_creation_stores_new_specs_and_equipment_in_the_same_format(): void
    {
        $this->admin();
        $source = $this->listing();
        $data = $source->only(['user_id', 'description', 'category', 'type', 'etat', 'price_dzd',
            'currency', 'type_offre', 'status', 'pays', 'wilaya', 'specs']);
        $data['title'] = 'Nouvelle annonce complète';

        Livewire::test(CreateListing::class)->fillForm($data)
            ->call('create')->assertHasNoFormErrors();

        $created = Listing::where('title', $data['title'])->firstOrFail();
        $this->assertSame('Portugal', $created->getSpec('general', 'immatriculation_autre'));
        $this->assertSame('Embase', $created->getSpec('motorisation', 'type_helice'));
        $this->assertEquals(2, $created->getSpec('reservoirs', 'nombre_reservoirs'));
        $this->assertEquals(420, $created->getSpec('reservoirs', 'capacite_totale'));
        $this->assertSame($source->getSpec('tags', 'equipement'), $created->getSpec('tags', 'equipement'));
        $this->assertSame($source->getSpec('tags', 'extras'), $created->getSpec('tags', 'extras'));
    }
}
