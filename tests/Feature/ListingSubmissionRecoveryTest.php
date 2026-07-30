<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Reprise d'un envoi d'annonce dont la réponse s'est perdue.
 *
 * Une annonce transporte jusqu'à 20 photos : la passerelle peut couper la
 * connexion pendant le redimensionnement alors que tout est enregistré. Le
 * navigateur croyait alors à un échec, et renvoyer créait un doublon.
 */
class ListingSubmissionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = '8f2a1c44-9b3e-4d21-a7c5-15d0e6b8f931';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.listing_disk', 'public'));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'        => 'Bateau de peche 6m',
            'description'  => 'En bon etat, moteur revise.',
            'category'     => 'boat',
            'type'         => 'bateau_peche',
            'price_dzd'    => 1_500_000,
            'currency'     => 'DZD',
            'type_offre'   => 'negociable',
            'etat'         => 'bon_etat',
            'wilaya'       => 'Alger',
            'client_token' => self::TOKEN,
            'images'       => [UploadedFile::fake()->image('photo.jpg', 800, 600)],
        ], $overrides);
    }

    public function test_a_submission_stores_the_client_token(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $this->actingAs($user)
            ->postJson(route('listings.store'), $this->payload())
            ->assertOk()
            ->assertJsonStructure(['redirect', 'message', 'listing_id']);

        $this->assertSame(self::TOKEN, Listing::sole()->client_token);
    }

    public function test_resending_the_same_token_does_not_create_a_duplicate(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $first = $this->actingAs($user)->postJson(route('listings.store'), $this->payload());
        $first->assertOk();

        // Le navigateur n'a jamais reçu la première réponse et renvoie tout.
        $second = $this->actingAs($user)->postJson(route('listings.store'), $this->payload());
        $second->assertOk();

        $this->assertSame(1, Listing::count());
        $this->assertSame($first->json('redirect'), $second->json('redirect'));
        $this->assertSame($first->json('listing_id'), $second->json('listing_id'));
    }

    public function test_the_status_route_reports_a_completed_submission(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $created = $this->actingAs($user)->postJson(route('listings.store'), $this->payload());

        $this->actingAs($user)
            ->getJson(route('listings.submission-status', ['token' => self::TOKEN]))
            ->assertOk()
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('listing_id', $created->json('listing_id'))
            ->assertJsonPath('redirect', $created->json('redirect'));
    }

    public function test_an_unknown_token_is_pending_not_an_error(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $this->actingAs($user)
            ->getJson(route('listings.submission-status', ['token' => self::TOKEN]))
            ->assertOk()
            ->assertJsonPath('status', 'pending');
    }

    public function test_a_token_belonging_to_someone_else_is_not_disclosed(): void
    {
        $owner  = User::factory()->create(['phone' => '+213670000000']);
        $other  = User::factory()->create(['phone' => '+213670000001']);

        $this->actingAs($owner)->postJson(route('listings.store'), $this->payload())->assertOk();

        $this->actingAs($other)
            ->getJson(route('listings.submission-status', ['token' => self::TOKEN]))
            ->assertOk()
            ->assertJsonPath('status', 'pending');
    }

    public function test_a_submission_without_a_token_still_works(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $this->actingAs($user)
            ->postJson(route('listings.store'), $this->payload(['client_token' => null]))
            ->assertOk();

        $this->assertNull(Listing::sole()->client_token);
    }

    /**
     * `type_offre` est facultatif à la validation mais NOT NULL en base :
     * l'omettre renvoyait un 500 et l'annonce était perdue.
     */
    public function test_a_submission_without_type_offre_does_not_fail(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $this->actingAs($user)
            ->postJson(route('listings.store'), $this->payload(['type_offre' => null]))
            ->assertOk();

        $this->assertSame('negociable', Listing::sole()->type_offre);
    }

    public function test_the_api_survives_a_missing_type_offre_too(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->payload(['type_offre' => null]))
            ->assertCreated();

        $this->assertSame('negociable', Listing::sole()->type_offre);
    }

    public function test_the_api_returns_the_existing_listing_on_a_resend(): void
    {
        $user = User::factory()->create(['phone' => '+213670000000']);

        $first = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->payload());
        $first->assertCreated();

        $second = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->payload());
        $second->assertOk()->assertJsonPath('already_created', true);

        $this->assertSame(1, Listing::count());
        $this->assertSame($first->json('listing.id'), $second->json('listing.id'));
    }
}
