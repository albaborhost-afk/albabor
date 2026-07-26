<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_inline_editing_controls(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'phone' => '+213670000000',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSeeText(__('messages.profile_completion'));
        $response->assertSeeText(__('messages.open_full_editor'));
        $response->assertSeeText($user->email);
    }

    public function test_user_can_update_personal_info_from_profile_page(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => ' Ayoub Benderdouch ',
            'phone' => '+213 670 123 456',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', __('messages.profile_updated'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Ayoub Benderdouch',
            'phone' => '0670123456',
            'phone_country_code' => '+213',
        ]);
    }

    public function test_vendor_users_see_vendor_status_on_profile_page(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'account_type' => 'vendor',
            'phone' => '+213550000000',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSeeText(__('messages.vendor'));
    }
}
