<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelLoginTest extends TestCase
{
    use RefreshDatabase;

    private function panel(string $id = 'admin'): void
    {
        Filament::setCurrentPanel(Filament::getPanel($id));
    }

    public function test_admin_email_is_normalized_before_validation_and_authentication(): void
    {
        $this->panel();
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'CorrectCASE@16',
            'account_type' => 'admin',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => '  ADMIN@Example.Test  ', 'password' => 'CorrectCASE@16'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_vendor_login_uses_the_same_email_normalization(): void
    {
        $this->panel('vendeur');
        $vendor = User::factory()->create([
            'email' => 'vendor@example.test',
            'password' => 'CorrectCASE@16',
            'account_type' => 'vendor',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'Vendor@EXAMPLE.TEST', 'password' => 'CorrectCASE@16'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($vendor);
    }

    public function test_password_case_is_not_changed(): void
    {
        $this->panel();
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'CorrectCASE@16',
            'account_type' => 'admin',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'ADMIN@EXAMPLE.TEST', 'password' => 'correctcase@16'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_normal_users_cannot_access_the_admin_panel(): void
    {
        $this->panel();
        User::factory()->create([
            'email' => 'user@example.test',
            'password' => 'CorrectCASE@16',
            'account_type' => 'user',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'USER@EXAMPLE.TEST', 'password' => 'CorrectCASE@16'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_blocked_admins_still_cannot_log_in(): void
    {
        $this->panel();
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'CorrectCASE@16',
            'account_type' => 'admin',
            'is_blocked' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'ADMIN@EXAMPLE.TEST', 'password' => 'CorrectCASE@16'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}
