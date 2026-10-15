<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PrivateSellerMessagingTest extends TestCase
{
    use RefreshDatabase;

    private function listing(bool $mediation = false): Listing
    {
        $seller = User::factory()->create([
            'name' => 'Karim Confidentiel',
            'hide_name' => true,
            'phone' => '0676085441',
            'email' => 'compte.prive@example.dz',
        ]);

        return Listing::create([
            'user_id' => $seller->id,
            'title' => 'Bateau de peche 6m',
            'description' => 'En bon etat.',
            'category' => 'boat',
            'type' => 'bateau_peche',
            'price_dzd' => 1_500_000,
            'currency' => 'DZD',
            'etat' => 'bon_etat',
            'status' => 'active',
            'published_until' => now()->addYear(),
            'mediation_enabled' => $mediation,
            'numero_mobile' => '0555123456',
            'numero_whatsapp' => '0666987654',
            'contact_email' => 'annonce.prive@example.dz',
        ]);
    }

    private function assertPrivate(TestResponse $response): void
    {
        foreach (['Karim Confidentiel', '0676085441', '0555123456', '0666987654',
            'compte.prive@example.dz', 'annonce.prive@example.dz'] as $privateValue) {
            $response->assertDontSee($privateValue);
        }
    }

    public function test_web_messages_and_seller_replies_keep_the_seller_private(): void
    {
        $this->withoutVite();
        Notification::fake();
        $listing = $this->listing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->post(route('conversations.store', $listing), ['body' => 'Bonjour']);
        $conversation = Conversation::sole();
        $this->assertSame($listing->user_id, $conversation->seller_id);

        $this->actingAs($listing->user)
            ->post(route('conversations.reply', $conversation), ['body' => 'Toujours disponible.'])
            ->assertRedirect();

        $this->actingAs($buyer);
        $page = $this->get(route('conversations.show', $conversation))->assertOk()
            ->assertSeeText(User::ANONYMOUS_NAME)
            ->assertSee('Toujours disponible.');
        $this->assertPrivate($page);

        $messages = $this->getJson(route('conversations.messages', $conversation))->assertOk()
            ->assertJsonPath('1.sender.name', User::ANONYMOUS_NAME)
            ->assertJsonMissingPath('1.sender.phone');
        $this->assertPrivate($messages);
    }

    public function test_api_messages_and_seller_replies_keep_the_seller_private(): void
    {
        $this->withHeader('Accept-Language', 'fr');
        Notification::fake();
        $listing = $this->listing();
        $buyer = User::factory()->create();

        $created = $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/v1/conversations/listing/'.$listing->id, ['body' => 'Bonjour'])
            ->assertCreated();
        $this->assertPrivate($created);
        $conversation = Conversation::sole();

        $this->actingAs($listing->user, 'sanctum')
            ->postJson('/api/v1/conversations/'.$conversation->id.'/messages', ['body' => 'Toujours disponible.'])
            ->assertCreated();

        $received = $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/v1/conversations/'.$conversation->id)->assertOk()
            ->assertJsonPath('messages.1.body', 'Toujours disponible.')
            ->assertJsonPath('messages.1.sender.name', User::ANONYMOUS_NAME)
            ->assertJsonMissingPath('messages.1.sender.phone');
        $this->assertPrivate($received);
    }

    public function test_private_mediated_listings_keep_admin_messaging_without_exposing_contacts(): void
    {
        $this->withHeader('Accept-Language', 'fr');
        $this->withoutVite();
        $listing = $this->listing(true);
        $buyer = User::factory()->create();

        $page = $this->actingAs($buyer)->get(route('listings.show', $listing))->assertOk()
            ->assertSeeText('Message à l’administration')
            ->assertSee(route('mediation.create', $listing))
            ->assertDontSee(route('conversations.store', $listing));
        $this->assertPrivate($page);

        $this->post(route('conversations.store', $listing), ['body' => 'Bonjour'])
            ->assertRedirect(route('mediation.create', $listing));

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/v1/conversations/listing/'.$listing->id, ['body' => 'Bonjour'])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'mediation_required');
        $this->assertDatabaseCount('conversations', 0);

        $ticket = $this->postJson('/api/v1/mediation/'.$listing->id, ['message' => 'Bonjour'])
            ->assertCreated()
            ->assertJsonPath('ticket.seller.name', User::ANONYMOUS_NAME)
            ->assertJsonMissingPath('ticket.seller.phone');
        $this->assertPrivate($ticket);
    }
}
