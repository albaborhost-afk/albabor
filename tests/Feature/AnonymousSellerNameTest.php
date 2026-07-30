<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Réglage « publier sous Invité » : le nom et la photo d'un vendeur sont
 * masqués pour les tiers, mais restent visibles pour lui-même et l'admin.
 *
 * Le masquage se fait à la lecture de l'attribut : ces tests vérifient qu'un
 * point d'affichage oublié ne peut pas divulguer le nom.
 */
class AnonymousSellerNameTest extends TestCase
{
    use RefreshDatabase;

    private function seller(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name'      => 'Karim Benali',
            'hide_name' => true,
            'phone'     => '+213670000000',
        ], $attributes));
    }

    private function activeListing(User $seller): Listing
    {
        return Listing::create([
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
        ]);
    }

    public function test_guest_sees_the_anonymous_name_instead_of_the_real_one(): void
    {
        $seller = $this->seller();

        $this->assertSame(User::ANONYMOUS_NAME, $seller->fresh()->name);
        $this->assertSame(User::ANONYMOUS_NAME, $seller->fresh()->toArray()['name']);
    }

    public function test_another_user_sees_the_anonymous_name(): void
    {
        $seller = $this->seller();
        $buyer  = User::factory()->create(['phone' => '+213670000001']);

        $this->actingAs($buyer);

        $this->assertSame(User::ANONYMOUS_NAME, $seller->fresh()->name);
    }

    public function test_the_seller_still_sees_their_own_real_name(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller);

        $this->assertSame('Karim Benali', $seller->fresh()->name);
    }

    public function test_an_admin_still_sees_the_real_name(): void
    {
        $seller = $this->seller();
        $admin  = User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000002',
        ]);

        $this->actingAs($admin);

        $this->assertSame('Karim Benali', $seller->fresh()->name);
    }

    public function test_with_real_name_reveals_the_name_for_the_accounts_own_responses(): void
    {
        $seller = $this->seller();

        $this->assertSame('Karim Benali', $seller->fresh()->withRealName()->name);
        $this->assertSame('Karim Benali', $seller->fresh()->real_name);
    }

    public function test_the_setting_off_changes_nothing(): void
    {
        $seller = $this->seller(['hide_name' => false]);

        $this->assertSame('Karim Benali', $seller->fresh()->name);
        $this->assertFalse($seller->fresh()->identityMasked());
    }

    public function test_the_profile_picture_is_hidden_together_with_the_name(): void
    {
        $seller = $this->seller(['profile_picture' => 'profile-pictures/karim.jpg']);

        $this->assertNull($seller->fresh()->profile_picture_url);

        $this->actingAs($seller);
        $this->assertNotNull($seller->fresh()->profile_picture_url);
    }

    public function test_the_profile_picture_route_is_not_a_back_door(): void
    {
        $seller = $this->seller(['profile_picture' => 'profile-pictures/karim.jpg']);

        $this->get(route('profile.picture', ['userId' => $seller->id]))->assertNotFound();
    }

    public function test_the_listing_page_never_prints_the_real_name(): void
    {
        $this->withoutVite();

        $seller  = $this->seller();
        $listing = $this->activeListing($seller);

        $response = $this->get(route('listings.show', $listing));

        $response->assertOk();
        $response->assertDontSee('Karim Benali');
        $response->assertSeeText(User::ANONYMOUS_NAME);
    }

    public function test_the_public_seller_profile_lists_the_listings_without_the_name(): void
    {
        $this->withoutVite();

        $seller  = $this->seller();
        $listing = $this->activeListing($seller);

        $response = $this->get(route('sellers.show', $seller));

        $response->assertOk();
        $response->assertDontSee('Karim Benali');
        $response->assertSeeText($listing->title);
    }

    public function test_the_api_vendor_profile_masks_the_name(): void
    {
        $seller = $this->seller();
        $this->activeListing($seller);

        $response = $this->getJson('/api/v1/vendors/' . $seller->id);

        $response->assertOk();
        $response->assertJsonPath('user.name', User::ANONYMOUS_NAME);
        $response->assertJsonPath('user.hide_name', true);
        $response->assertJsonPath('stats.active_listings', 1);
    }

    public function test_the_account_sees_its_own_real_name_through_the_api(): void
    {
        $seller = $this->seller();

        $response = $this->actingAs($seller, 'sanctum')->getJson('/api/v1/profile');

        $response->assertOk();
        $response->assertJsonPath('user.name', 'Karim Benali');
        $response->assertJsonPath('user.hide_name', true);
    }

    public function test_the_setting_can_be_toggled_from_the_web_profile(): void
    {
        $seller = $this->seller(['hide_name' => false]);

        $this->actingAs($seller)->put(route('profile.update'), [
            'name'      => 'Karim Benali',
            'phone'     => '+213670000000',
            'hide_name' => 1,
        ])->assertRedirect();

        $this->assertTrue($seller->fresh()->hidesName());

        $this->actingAs($seller)->put(route('profile.update'), [
            'name'      => 'Karim Benali',
            'phone'     => '+213670000000',
            'hide_name' => 0,
        ])->assertRedirect();

        $this->assertFalse($seller->fresh()->hidesName());
    }

    public function test_the_setting_can_be_toggled_from_the_api(): void
    {
        $seller = $this->seller(['hide_name' => false]);

        $this->actingAs($seller, 'sanctum')->putJson('/api/v1/profile', [
            'name'      => 'Karim Benali',
            'phone'     => '+213670000000',
            'hide_name' => true,
        ])->assertOk();

        $this->assertTrue($seller->fresh()->hidesName());
    }

    public function test_an_app_that_omits_the_field_does_not_reset_the_setting(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller, 'sanctum')->putJson('/api/v1/profile', [
            'name'  => 'Karim Benali',
            'phone' => '+213670000000',
        ])->assertOk();

        $this->assertTrue($seller->fresh()->hidesName());
    }
}
