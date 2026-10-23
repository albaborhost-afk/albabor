<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ResendTransport;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
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

    public function test_the_resend_mail_transport_can_be_created(): void
    {
        config(['services.resend.key' => 're_test_not_a_real_key']);

        $this->assertInstanceOf(ResendTransport::class, Mail::mailer('resend')->getSymfonyTransport());
    }

    public function test_the_mobile_request_renders_a_usable_email_with_the_configured_sender(): void
    {
        config([
            'mail.default' => 'array',
            'mail.from.address' => 'no-reply@albabor.com',
            'mail.from.name' => 'AlBabor',
        ]);

        $user = $this->user();

        $this->postJson('https://albabor.com/api/v1/auth/forgot-password', ['email' => '  KARIM@EXEMPLE.COM  '])
            ->assertOk();

        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        $email = $messages->first()->getOriginalMessage();
        $this->assertSame('no-reply@albabor.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('AlBabor', $email->getFrom()[0]->getName());
        $this->assertSame($user->email, $email->getTo()[0]->getAddress());
        $this->assertNotEmpty($email->getHtmlBody());

        preg_match('~https://albabor[.]com/reset-password/([a-zA-Z0-9]+)[?]email=([^[:space:])]+)~', $email->getTextBody(), $link);
        $this->assertNotEmpty($link, 'The email must contain an HTTPS reset link on albabor.com.');
        $this->assertSame($user->email, rawurldecode($link[2]));
        $this->assertTrue(Password::tokenExists($user, $link[1]));
    }

    public static function emailLocales(): array
    {
        return [
            'French' => ['fr', 'Réinitialisez votre mot de passe AlBabor', 'Reprenons le cap ensemble.'],
            'English' => ['en', 'Reset your AlBabor password', 'Let’s get you back on board.'],
            'Spanish' => ['es', 'Restablece tu contraseña de AlBabor', 'Volvamos a navegar juntos.'],
        ];
    }

    #[DataProvider('emailLocales')]
    public function test_the_reset_email_uses_the_brand_and_requested_language(string $locale, string $subject, string $title): void
    {
        config([
            'app.url' => 'https://albabor.com',
            'mail.default' => 'array',
            'auth.passwords.users.expire' => 17,
        ]);
        $user = $this->user();

        $this->withHeader('Accept-Language', $locale)
            ->postJson('https://albabor.com/api/v1/auth/forgot-password', ['email' => $user->email])
            ->assertOk();

        $email = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();

        $this->assertSame($subject, $email->getSubject());
        $this->assertStringContainsString('lang="'.$locale.'"', $email->getHtmlBody());
        $this->assertStringContainsString('src="https://albabor.com/images/logo-full.png"', $email->getHtmlBody());
        $this->assertStringContainsString('alt="AlBabor"', $email->getHtmlBody());
        $this->assertStringContainsString($title, $email->getHtmlBody());
        $this->assertStringContainsString($title, $email->getTextBody());
        $this->assertMatchesRegularExpression('/17 (minutes|minutos)/', $email->getTextBody());
        $this->assertStringNotContainsString('passwords.email.', $email->getHtmlBody());
        $this->assertStringNotContainsString('<table', $email->getTextBody());
    }

    private function failMailDelivery(): void
    {
        Exceptions::fake();
        config(['mail.default' => 'array']);

        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send')->once()
            ->andThrow(new TransportException('The albabor.com domain is not verified.'));

        Mail::mailer('array')->setSymfonyTransport($transport);
    }

    public function test_a_mail_provider_failure_returns_to_the_web_form_with_an_error(): void
    {
        $user = $this->user();
        $this->failMailDelivery();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['email' => __('passwords.mail_unavailable')])
            ->assertSessionHasInput('email', $user->email)
            ->assertSessionMissing('status');

        Exceptions::assertReported(TransportException::class);
    }

    public function test_a_mail_provider_failure_returns_a_useful_mobile_error(): void
    {
        $user = $this->user();
        $this->failMailDelivery();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
            ->assertStatus(503)
            ->assertJsonPath('errors.email.0', __('passwords.mail_unavailable'))
            ->assertDontSee('domain is not verified');

        Exceptions::assertReported(TransportException::class);
    }
}
