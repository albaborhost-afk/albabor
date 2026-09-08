<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use App\Notifications\NewConversationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SeptemberModificationTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attributes = []): Listing
    {
        return Listing::create(array_replace([
            'user_id' => User::factory()->create()->id, 'title' => 'Bateau de test',
            'description' => 'Description du bateau', 'category' => 'boat', 'type' => 'yacht',
            'price_dzd' => 500000, 'currency' => 'DZD', 'type_offre' => 'negociable',
            'etat' => 'bon_etat', 'status' => 'active', 'pays' => 'Algérie', 'wilaya' => 'Alger',
            'specs' => [
                'general' => ['fabricant' => 'Jeanneau', 'annee_construction' => '2024'],
                'dimensions' => ['longueur' => '12.5', 'largeur' => '4.2'],
                'motorisation' => ['propulsion' => 'In-bord', 'type_carburant' => 'Diesel',
                    'nombre_moteurs' => '2', 'puissance_par_moteur' => '150', 'type_helice' => 'IPS 360°'],
            ],
        ], $attributes));
    }

    private function payload(Listing $listing, array $overrides = []): array
    {
        return array_replace($listing->only(['title', 'description', 'category', 'type', 'price_dzd', 'currency', 'type_offre', 'etat', 'pays', 'wilaya', 'specs']), $overrides);
    }

    public function test_web_and_api_apply_numeric_and_motor_filters_together(): void
    {
        $match = $this->listing();
        $this->listing(['specs' => ['dimensions' => ['longueur' => '9.9']]]);
        $filters = ['length_min' => 10, 'length_max' => 13, 'width_min' => 4, 'year_min' => 2020,
            'year_max' => 2025, 'fabricant' => 'Jeanneau', 'propulsion' => 'In-bord', 'fuel_type' => 'Diesel',
            'engine_count' => 2, 'power_min' => 250, 'power_max' => 350, 'drive_type' => 'IPS 360°', 'pays' => 'Algérie'];
        $this->getJson('/api/v1/listings?'.http_build_query($filters))->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $match->id);
        $this->get('/annonces?'.http_build_query($filters))->assertOk()->assertViewHas('listings', fn ($listings) => $listings->total() === 1 && $listings->first()->id === $match->id);
        $this->getJson('/api/v1/listings?engine_count=1')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/listings?power_max=200')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_countries_exclude_pending_and_expired_listings_and_filter_consistently(): void
    {
        $this->listing();
        $spanish = $this->listing(['pays' => 'Espagne', 'wilaya' => 'Alicante']);
        $this->listing(['pays' => 'France', 'status' => 'pending_review']);
        $this->listing(['pays' => 'France', 'published_until' => now()->subDay()]);
        $this->getJson('/api/v1/listings/countries')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.country', 'Algérie')->assertJsonPath('data.1.flag', '🇪🇸')
            ->assertJsonPath('data.1.count', 1)->assertJsonPath('data.1.listings.0.id', $spanish->id);
        $this->getJson('/api/v1/listings?pays=Espagne')->assertOk()->assertJsonCount(1, 'data');
        $this->get('/annonces?pays=Espagne')->assertOk()->assertViewHas('listings', fn ($listings) => $listings->total() === 1);
    }

    public function test_tank_and_power_totals_are_computed_from_inputs_and_preserve_equipment(): void
    {
        $listing = $this->listing(['specs' => [
            'reservoirs' => ['nombre_reservoirs' => '2', 'reservoir_carburant' => '150', 'reservoir_eau_douce' => '100', 'stockage' => '20', 'total_carburant' => 150, 'capacite_totale' => 999],
            'motorisation' => ['nombre_moteurs' => '2', 'puissance_par_moteur' => '140', 'puissance_totale' => 140],
            'tags' => ['extras' => ['Annexe', 'Barbecue extérieur', 'Annexe', 'Équipement personnel']],
        ]]);
        $listing->refresh();
        $this->assertEquals(300, $listing->getSpec('reservoirs', 'total_carburant'));
        $this->assertEquals(420, $listing->getSpec('reservoirs', 'capacite_totale'));
        $this->assertEquals(280, $listing->getSpec('motorisation', 'puissance_totale'));
        $this->assertSame(['Annexe', 'Barbecue extérieur', 'Équipement personnel'], $listing->getSpec('tags', 'extras'));
        $this->get('/annonces/'.$listing->id)->assertOk()->assertSee('Carburant total')->assertSee('Équipement personnel');
    }

    public function test_api_edit_saves_received_offer_registration_drive_and_private_mode(): void
    {
        $listing = $this->listing();
        $seller = $listing->user;
        $specs = $listing->specs;
        $specs['general']['immatriculation'] = 'Autre';
        $specs['general']['immatriculation_autre'] = 'Portugal';
        $specs['tags']['extras'] = ['Annexe'];
        $this->actingAs($seller, 'sanctum')->putJson('/api/v1/listings/'.$listing->id,
            $this->payload($listing, ['type_offre' => 'offert', 'price_dzd' => 950000, 'hide_name' => true, 'specs' => $specs]))
            ->assertOk()->assertJsonPath('listing.price_dzd', 950000)->assertJsonPath('listing.specs.general.immatriculation_autre', 'Portugal')
            ->assertJsonPath('listing.specs.motorisation.type_helice', 'IPS 360°');
        $this->assertTrue($seller->fresh()->hide_name);
        $this->actingAs($seller, 'sanctum')->putJson('/api/v1/listings/'.$listing->id, $this->payload($listing))->assertOk();
        $this->assertTrue($seller->fresh()->hide_name);
    }

    public function test_private_messages_notify_the_recipient_without_revealing_identity(): void
    {
        Notification::fake();
        $seller = User::factory()->create(['hide_name' => true]);
        $listing = $this->listing(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/v1/conversations/listing/'.$listing->id, ['body' => 'Bonjour'])
            ->assertCreated()->assertJsonPath('conversation.seller.name', 'Privé');
        Notification::assertSentTo($seller, NewConversationMessage::class);
        Notification::assertNotSentTo($buyer, NewConversationMessage::class);
    }

    public function test_mediation_uses_administration_instead_of_creating_a_direct_conversation(): void
    {
        Notification::fake();
        $listing = $this->listing(['mediation_enabled' => true]);
        $this->actingAs(User::factory()->create(), 'sanctum')->postJson('/api/v1/conversations/listing/'.$listing->id, ['body' => 'Bonjour'])
            ->assertStatus(422)->assertJsonPath('code', 'mediation_required');
        $this->assertDatabaseCount('conversations', 0);
        Notification::assertNothingSent();
    }

    public function test_pending_listing_has_owner_actions_and_remains_hidden_from_buyers(): void
    {
        $listing = $this->listing(['status' => 'pending_review']);
        $this->get('/annonces/'.$listing->id)->assertNotFound();
        $this->actingAs($listing->user)->get(route('listings.my'))->assertOk()
            ->assertSee(route('listings.show', $listing))->assertSee(route('listings.edit', $listing))->assertSee(route('listings.destroy', $listing));
    }
    public function test_country_groups_preserve_private_contact_protection(): void
    {
        $seller = User::factory()->create(['hide_name' => true, 'name' => 'Private Seller']);
        $this->listing(['user_id' => $seller->id, 'numero_mobile' => '+213555123456', 'contact_email' => 'secret@example.test']);
        $response = $this->getJson('/api/v1/listings/countries')->assertOk();
        $response->assertJsonPath('data.0.listings.0.user.name', 'Privé')
            ->assertJsonMissingPath('data.0.listings.0.numero_mobile')
            ->assertJsonMissingPath('data.0.listings.0.contact_email')
            ->assertJsonMissingPath('data.0.listings.0.user.email');
    }

    public function test_mobile_multipart_edit_clears_fields_and_preserves_price_display_unit(): void
    {
        $listing = $this->listing(['price_display_unit' => 'million', 'contact_email' => 'old@example.test']);
        $this->actingAs($listing->user, 'sanctum')->post('/api/v1/listings/'.$listing->id,
            $this->payload($listing, ['_method' => 'PUT', 'contact_email' => '',
                'specs' => ['reservoirs' => ['nombre_reservoirs' => '2', 'reservoir_carburant' => '150'], 'tags' => ['extras' => '']]]), ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('listing.specs.reservoirs.total_carburant', 300);
        $this->assertNull($listing->fresh()->contact_email);
        $this->assertSame('million', $listing->fresh()->price_display_unit);
    }

    public function test_mobile_payment_choices_use_the_same_destinations_as_the_website(): void
    {
        $this->getJson('/api/v1/settings/payment-methods')->assertOk()
            ->assertJsonPath('holder', config('payment_methods.holder'))
            ->assertJsonPath('methods.0.detail', config('payment_methods.methods.baridimob.detail'))
            ->assertJsonPath('methods.1.key', 'bank_transfer')->assertJsonCount(3, 'methods');
    }

    public function test_existing_tank_data_is_displayed_correctly_without_an_edit(): void
    {
        $listing = $this->listing();
        \DB::table('listings')->where('id', $listing->id)->update(['specs' => json_encode([
            'reservoirs' => ['nombre_reservoirs' => 2, 'reservoir_carburant' => 150, 'reservoir_eau_douce' => 100, 'stockage' => 20, 'capacite_totale' => 270],
        ])]);
        $this->getJson('/api/v1/listings/'.$listing->id)->assertOk()->assertJsonPath('listing.specs.reservoirs.capacite_totale', 420);
        $this->assertFalse($listing->fresh()->isDirty('specs'));
    }

    public function test_web_creation_prefills_contact_details_and_offer_label_is_visible(): void
    {
        $listing = $this->listing(['type_offre' => 'offert']);
        $user = $listing->user;
        $user->update(['phone' => '+213555123456', 'free_publishing' => true]);
        $this->actingAs($user)->get(route('listings.create'))->assertOk()
            ->assertSee('data-old-phone="+213555123456"', false)->assertSee($user->email);
        $this->get(route('listings.show', $listing))->assertOk()->assertSee('Offert (offre reçue)');
    }

}
