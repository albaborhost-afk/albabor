<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Connexion Google mobile.
 *
 * Le point d'entrée acceptait tout id_token que Google jugeait valide, sans
 * regarder POUR QUI il avait été émis ni si l'adresse était vérifiée. Deux
 * conséquences : un jeton destiné à une autre application ouvrait le compte
 * AlBabor correspondant, et une adresse non vérifiée suffisait à se faire
 * rattacher au compte d'un vendeur.
 */
class GoogleSignInSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const ALLOWED_AUD = '364258394169-6osv054m4a6ckphm8l78d7arrah8se2p.apps.googleusercontent.com';

    private function fakeGoogleReplies(array $overrides = []): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(array_merge([
                'iss'            => 'https://accounts.google.com',
                'aud'            => self::ALLOWED_AUD,
                'sub'            => 'google-user-1',
                'email'          => 'victime@exemple.com',
                'email_verified' => 'true',
                'name'           => 'Karim Benali',
            ], $overrides), 200),
        ]);
    }

    private function attempt(): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/auth/google', ['id_token' => 'peu-importe']);
    }

    // ── Ce qui doit être refusé ──────────────────────────────────────────────

    public function test_a_token_issued_for_another_application_is_refused(): void
    {
        // Le scénario de reprise de compte : un jeton parfaitement valide,
        // mais émis pour l'application de quelqu'un d'autre.
        $this->fakeGoogleReplies(['aud' => 'application-d-un-tiers.apps.googleusercontent.com']);

        $this->attempt()->assertStatus(401);

        $this->assertSame(0, User::count(), 'Aucun compte ne doit être créé.');
    }

    public function test_an_unverified_email_is_refused(): void
    {
        $this->fakeGoogleReplies(['email_verified' => 'false']);

        $this->attempt()->assertStatus(401);
        $this->assertSame(0, User::count());
    }

    public function test_an_unexpected_issuer_is_refused(): void
    {
        $this->fakeGoogleReplies(['iss' => 'https://accounts.exemple.com']);

        $this->attempt()->assertStatus(401);
    }

    public function test_a_token_without_email_is_refused(): void
    {
        $this->fakeGoogleReplies(['email' => null]);

        $this->attempt()->assertStatus(401);
    }

    public function test_an_existing_account_is_not_taken_over_by_a_foreign_token(): void
    {
        $victim = User::factory()->create([
            'email' => 'victime@exemple.com',
            'phone' => '+213670000050',
        ]);

        $this->fakeGoogleReplies(['aud' => 'application-d-un-tiers.apps.googleusercontent.com']);

        $this->attempt()->assertStatus(401);

        $this->assertNull($victim->fresh()->google_id, 'Le compte ne doit pas avoir été rattaché.');
    }

    // ── Ce qui doit continuer de marcher ─────────────────────────────────────

    public function test_a_legitimate_token_signs_the_user_in(): void
    {
        $this->fakeGoogleReplies();

        $response = $this->attempt()->assertOk();

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame('victime@exemple.com', $response->json('user.email'));

        $user = User::sole();
        $this->assertSame('google-user-1', $user->google_id);
    }

    public function test_a_boolean_true_for_email_verified_is_accepted(): void
    {
        // Selon le point d'entrée, Google renvoie true ou la chaîne "true".
        $this->fakeGoogleReplies(['email_verified' => true]);

        $this->attempt()->assertOk();
    }

    public function test_an_existing_account_is_linked_when_the_email_is_verified(): void
    {
        $user = User::factory()->create([
            'email' => 'victime@exemple.com',
            'phone' => '+213670000051',
        ]);

        $this->fakeGoogleReplies();

        $this->attempt()->assertOk();

        $this->assertSame('google-user-1', $user->fresh()->google_id);
        $this->assertSame(1, User::count(), 'Aucun doublon de compte.');
    }

    public function test_a_blocked_account_cannot_sign_in(): void
    {
        User::factory()->create([
            'email'      => 'victime@exemple.com',
            'phone'      => '+213670000052',
            'is_blocked' => true,
        ]);

        $this->fakeGoogleReplies();

        $this->attempt()->assertStatus(403);
    }

    public function test_the_route_is_rate_limited(): void
    {
        $this->fakeGoogleReplies(['aud' => 'tiers.apps.googleusercontent.com']);

        // 10 tentatives par minute : la 11e doit être refusée.
        for ($i = 0; $i < 10; $i++) {
            $this->attempt();
        }

        $this->attempt()->assertStatus(429);
    }
}
