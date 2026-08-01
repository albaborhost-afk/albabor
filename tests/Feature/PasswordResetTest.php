<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Réinitialisation de mot de passe.
 *
 * La route `password.reset` n'existait nulle part. La notification de Laravel
 * construisant son lien avec elle, l'envoi levait une RouteNotFoundException :
 * la demande renvoyait une erreur 500 et personne ne pouvait récupérer son
 * compte. Ces tests couvrent la chaîne entière.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'email'    => 'karim@exemple.com',
            'password' => 'ancien-mot-de-passe',
            'phone'    => '+213670000060',
        ]);
    }

    public function test_the_request_page_opens(): void
    {
        $this->withoutVite();

        $this->get(route('password.request'))->assertOk();
    }

    public function test_asking_for_a_link_sends_the_notification(): void
    {
        Notification::fake();

        $user = $this->user();

        $this->post(route('password.email'), ['email' => 'karim@exemple.com'])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * Le test qui aurait attrapé la panne : construire le lien exigeait une
     * route absente, ce qui levait une exception au moment de l'envoi.
     */
    public function test_the_notification_can_build_its_link(): void
    {
        Notification::fake();

        $user = $this->user();

        $this->post(route('password.email'), ['email' => 'karim@exemple.com']);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);
            $this->assertStringContainsString('reset-password', $mail->actionUrl);

            return true;
        });
    }

    public function test_the_reset_form_opens_with_a_token(): void
    {
        $this->withoutVite();

        $this->get(route('password.reset', ['token' => 'un-jeton', 'email' => 'karim@exemple.com']))
            ->assertOk()
            ->assertSee('un-jeton', false);
    }

    public function test_the_password_is_actually_changed(): void
    {
        Notification::fake();

        $user = $this->user();

        $this->post(route('password.email'), ['email' => 'karim@exemple.com']);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'karim@exemple.com',
            'password'              => 'nouveau-mot-de-passe-123',
            'password_confirmation' => 'nouveau-mot-de-passe-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nouveau-mot-de-passe-123', $user->fresh()->password));
    }

    public function test_existing_sessions_are_revoked(): void
    {
        Notification::fake();

        $user = $this->user();
        $user->createToken('mobile');

        $this->assertSame(1, $user->tokens()->count());

        $this->post(route('password.email'), ['email' => 'karim@exemple.com']);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'karim@exemple.com',
            'password'              => 'nouveau-mot-de-passe-123',
            'password_confirmation' => 'nouveau-mot-de-passe-123',
        ]);

        // Réinitialiser après un vol de compte doit mettre l'intrus dehors.
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_an_invalid_token_is_refused(): void
    {
        $user = $this->user();

        $this->post(route('password.update'), [
            'token'                 => 'jeton-invente',
            'email'                 => 'karim@exemple.com',
            'password'              => 'nouveau-mot-de-passe-123',
            'password_confirmation' => 'nouveau-mot-de-passe-123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('ancien-mot-de-passe', $user->fresh()->password));
    }

    public function test_a_mismatched_confirmation_is_refused(): void
    {
        $this->user();

        $this->post(route('password.update'), [
            'token'                 => 'peu-importe',
            'email'                 => 'karim@exemple.com',
            'password'              => 'nouveau-mot-de-passe-123',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors('password');
    }

    public function test_the_mobile_api_can_ask_for_a_link_too(): void
    {
        Notification::fake();

        $user = $this->user();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'karim@exemple.com'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
