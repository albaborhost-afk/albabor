<?php

namespace Tests\Feature;

use App\Filament\Resources\ListingResource\Pages\EditListing;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\Listing;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSellerPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function listing(User $seller): Listing
    {
        return Listing::create([
            'user_id' => $seller->id,
            'title' => 'Bateau de pêche',
            'description' => 'Bateau en bon état.',
            'category' => 'boat',
            'type' => 'bateau_peche',
            'etat' => 'bon_etat',
            'price_dzd' => 1500000,
            'currency' => 'DZD',
            'status' => 'active',
            'published_until' => now()->addYear(),
            'numero_mobile' => '0555123456',
            'numero_whatsapp' => '0666987654',
            'contact_email' => 'private-contact@example.test',
        ]);
    }

    public function test_admin_enabling_private_profile_hides_all_existing_listings_and_keeps_messaging(): void
    {
        $this->withHeader('Accept-Language', 'fr');
        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create(['account_type' => 'admin']);
        $seller = User::factory()->create([
            'name' => 'Identité confidentielle',
            'hide_name' => false,
            'phone' => '0676123456',
        ]);
        $listings = [$this->listing($seller), $this->listing($seller)];
        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $seller->getRouteKey()])
            ->assertFormSet(['name' => 'Identité confidentielle', 'hide_name' => false])
            ->fillForm(['hide_name' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($seller->fresh()->hasPrivateProfile());
        $this->assertFalse($admin->fresh()->hasPrivateProfile());
        $this->assertSame('Identité confidentielle', $seller->fresh()->getRawOriginal('name'));
        Livewire::test(EditUser::class, ['record' => $seller->getRouteKey()])
            ->assertFormSet(['name' => 'Identité confidentielle', 'hide_name' => true]);

        $buyer = User::factory()->create();
        $this->actingAs($buyer);

        foreach ($listings as $listing) {
            $this->get(route('listings.show', $listing))->assertOk()
                ->assertSeeText(User::ANONYMOUS_NAME)
                ->assertSee(route('conversations.store', $listing))
                ->assertDontSee('Identité confidentielle')
                ->assertDontSee('0676123456')
                ->assertDontSee('0555123456')
                ->assertDontSee('0666987654')
                ->assertDontSee('private-contact@example.test');

            $this->getJson('/api/v1/listings/'.$listing->id)->assertOk()
                ->assertJsonPath('listing.user.name', User::ANONYMOUS_NAME)
                ->assertJsonMissingPath('listing.numero_mobile')
                ->assertJsonMissingPath('listing.numero_whatsapp')
                ->assertJsonMissingPath('listing.contact_email')
                ->assertJsonMissingPath('listing.user.phone');
        }

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/v1/conversations/listing/'.$listings[0]->id, ['body' => 'Bonjour, est-il disponible ?'])
            ->assertCreated()
            ->assertJsonPath('conversation.seller.name', User::ANONYMOUS_NAME)
            ->assertJsonMissingPath('conversation.seller.phone');
    }

    public function test_admin_can_disable_private_profile_without_erasing_seller_details(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create(['account_type' => 'admin']);
        $seller = User::factory()->create([
            'name' => 'Identité conservée',
            'hide_name' => true,
            'phone' => '0676123456',
        ]);
        $listing = $this->listing($seller);
        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $seller->getRouteKey()])
            ->assertFormSet(['name' => 'Identité conservée', 'hide_name' => true])
            ->fillForm(['hide_name' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($seller->fresh()->hasPrivateProfile());
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/v1/listings/'.$listing->id)->assertOk()
            ->assertJsonPath('listing.user.name', 'Identité conservée')
            ->assertJsonPath('listing.user.phone', '0676123456')
            ->assertJsonPath('listing.numero_mobile', '0555123456')
            ->assertJsonPath('listing.numero_whatsapp', '0666987654')
            ->assertJsonPath('listing.contact_email', 'private-contact@example.test');
    }

    public function test_admin_listing_toggle_updates_the_sellers_profile_and_all_their_listings(): void
    {
        $this->withHeader('Accept-Language', 'fr');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create(['account_type' => 'admin']);
        $seller = User::factory()->create(['hide_name' => false]);
        $unrelated = User::factory()->create(['hide_name' => false]);
        $listing = $this->listing($seller);
        $otherListing = $this->listing($seller);
        $identity = $seller->fresh()->only(['name', 'email', 'phone', 'account_type']);
        $this->actingAs($admin);

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->assertFormSet(['hide_name' => false])
            ->fillForm(['hide_name' => true])
            ->call('save')->assertHasNoFormErrors();

        $this->assertTrue($seller->fresh()->hasPrivateProfile());
        $this->assertTrue($otherListing->fresh()->contactHiddenFor(null));
        $this->assertFalse($unrelated->fresh()->hasPrivateProfile());
        $this->assertFalse($admin->fresh()->hasPrivateProfile());
        $this->assertSame($identity, $seller->fresh()->only(array_keys($identity)));

        $this->actingAs(User::factory()->create(), 'sanctum');
        foreach ([$listing, $otherListing] as $record) {
            $this->getJson('/api/v1/listings/'.$record->id)->assertOk()
                ->assertJsonPath('listing.user.name', User::ANONYMOUS_NAME)
                ->assertJsonMissingPath('listing.numero_mobile')
                ->assertJsonMissingPath('listing.numero_whatsapp')
                ->assertJsonMissingPath('listing.contact_email');
        }
    }

    public function test_changing_the_selected_seller_loads_and_saves_only_the_new_sellers_privacy(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create(['account_type' => 'admin']);
        $original = User::factory()->create(['hide_name' => false]);
        $replacement = User::factory()->create(['hide_name' => true]);
        $listing = $this->listing($original);
        $this->actingAs($admin);

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->assertFormSet(['hide_name' => false])
            ->set('data.user_id', $replacement->id)
            ->assertFormSet(['hide_name' => true])
            ->fillForm(['hide_name' => false])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame($replacement->id, $listing->fresh()->user_id);
        $this->assertFalse($replacement->fresh()->hasPrivateProfile());
        $this->assertFalse($original->fresh()->hasPrivateProfile());
        $this->assertFalse($admin->fresh()->hasPrivateProfile());
    }

    public function test_an_unrelated_admin_listing_edit_preserves_the_private_profile(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['account_type' => 'admin']));
        $seller = User::factory()->create(['hide_name' => true]);
        $listing = $this->listing($seller);

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->assertFormSet(['hide_name' => true])
            ->fillForm(['price_dzd' => 2000000])
            ->call('save')->assertHasNoFormErrors();

        $this->assertTrue($seller->fresh()->hasPrivateProfile());
        $this->assertEquals(2000000, $listing->fresh()->price_dzd);
        $this->assertSame('0555123456', $listing->fresh()->numero_mobile);
    }

    public function test_invalid_admin_listing_changes_do_not_update_seller_privacy(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['account_type' => 'admin']));
        $seller = User::factory()->create(['hide_name' => false]);
        $listing = $this->listing($seller);

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['hide_name' => true, 'title' => ''])
            ->call('save')->assertHasFormErrors(['title']);

        $this->assertFalse($seller->fresh()->hasPrivateProfile());
        $this->assertSame('Bateau de pêche', $listing->fresh()->title);
    }
}
