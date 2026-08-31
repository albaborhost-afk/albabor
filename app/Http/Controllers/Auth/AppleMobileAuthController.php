<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Connexion Apple depuis l'application iOS.
 *
 * Même principe que {@see GoogleMobileAuthController} : un jeton d'identité ne
 * prouve quelque chose que si l'on vérifie qui l'a émis ET pour qui. Apple
 * n'offrant aucun point d'entrée de validation, la signature RS256 est
 * contrôlée ici contre les clés publiques publiées par Apple.
 *
 * Particularité d'Apple : l'adresse e-mail et le nom ne sont transmis QU'À LA
 * PREMIÈRE autorisation. Aux connexions suivantes, le jeton ne contient que
 * `sub`. C'est pourquoi `apple_id` est la clé de recherche, et le nom arrive du
 * client dans le corps de la requête.
 */
class AppleMobileAuthController extends Controller
{
    /** Émetteur légitime d'un identityToken Apple. */
    private const VALID_ISSUER = 'https://appleid.apple.com';

    /** Durée de cache des clés publiques Apple. */
    private const KEYS_CACHE_SECONDS = 86400;

    private const KEYS_CACHE_KEY = 'apple_auth_keys';

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity_token' => 'required|string',
            'full_name'      => 'nullable|string|max:255',
        ]);

        $claims = $this->decodeIdentityToken($validated['identity_token']);

        if ($claims === null) {
            return response()->json(['message' => 'Token Apple invalide'], 401);
        }

        if ($rejection = $this->rejectionReason($claims)) {
            Log::warning('Apple sign-in refused', [
                'reason' => $rejection,
                'aud'    => $claims['aud'] ?? null,
                'iss'    => $claims['iss'] ?? null,
            ]);

            return response()->json(['message' => 'Token Apple invalide'], 401);
        }

        $appleId = $claims['sub'];
        $email   = isset($claims['email']) ? strtolower(trim($claims['email'])) : null;

        $user = User::where('apple_id', $appleId)->first();

        if (! $user) {
            $user = $this->findOrCreateUser($appleId, $email, $validated['full_name'] ?? null, $claims);

            if ($user === null) {
                // Première connexion sans adresse : impossible de créer un
                // compte. Se produit si l'utilisateur a déjà autorisé l'app
                // puis que le compte a été supprimé côté AlBabor.
                return response()->json([
                    'message' => "Connexion Apple incomplète. Retirez AlBabor dans Réglages > "
                        ."Apple > Connexion avec Apple, puis réessayez.",
                ], 422);
            }
        }

        if ($user->isBlocked()) {
            return response()->json(['message' => 'Votre compte a été bloqué.'], 403);
        }

        $token = $user->createToken('mobile-apple')->plainTextToken;

        return response()->json([
            'token' => $token,
            // Son propre compte : il doit voir son vrai nom, pas « Invité ».
            'user'  => $user->withRealName(),
        ]);
    }

    /**
     * Rattache le compte à une adresse connue, ou en crée un nouveau.
     *
     * Retourne null si aucune adresse n'est disponible : sans elle, ni le
     * rattachement ni la création ne sont possibles.
     *
     * @param  array<string, mixed>  $claims
     */
    private function findOrCreateUser(string $appleId, ?string $email, ?string $fullName, array $claims): ?User
    {
        if ($email === null) {
            return null;
        }

        // Rattachement par adresse : n'est légitime que parce que
        // `email_verified` a été contrôlé plus haut. Sans cela, il suffirait de
        // créer un compte Apple avec l'adresse d'un vendeur pour prendre le sien.
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['apple_id' => $appleId]);

            return $user;
        }

        return User::create([
            'name'                => $this->resolveName($fullName, $email),
            'email'               => $email,
            'phone'               => null,
            'apple_id'            => $appleId,
            'password'            => bcrypt(Str::random(32)),
            'account_type'        => 'user',
            'verification_status' => 'none',
        ]);
    }

    /**
     * Apple ne transmet le nom qu'à la première autorisation, et l'application
     * le relaie. Sans lui, on dérive un libellé lisible de l'adresse plutôt que
     * d'afficher « Utilisateur » à tout le monde.
     */
    private function resolveName(?string $fullName, string $email): string
    {
        $fullName = trim((string) $fullName);

        if ($fullName !== '') {
            return $fullName;
        }

        // Les adresses relais Apple (xxxx@privaterelay.appleid.com) ne
        // contiennent aucun nom exploitable.
        if (Str::endsWith($email, '@privaterelay.appleid.com')) {
            return 'Utilisateur';
        }

        $local = Str::before($email, '@');

        return $local === '' ? 'Utilisateur' : Str::title(str_replace(['.', '_', '-'], ' ', $local));
    }

    /**
     * Vérifie la signature du jeton et retourne ses claims, ou null.
     *
     * Les clés d'Apple sont mises en cache, mais elles tournent : un échec est
     * donc réessayé une fois avec des clés fraîches avant d'être considéré
     * comme un vrai rejet.
     *
     * @return array<string, mixed>|null
     */
    private function decodeIdentityToken(string $identityToken): ?array
    {
        foreach ([true, false] as $useCache) {
            $keys = $this->applePublicKeys($useCache);

            if ($keys === null) {
                return null;
            }

            try {
                return (array) JWT::decode($identityToken, $keys);
            } catch (\Throwable $e) {
                if ($useCache) {
                    // Peut-être une clé ayant tourné : on retente sans cache.
                    continue;
                }

                Log::warning('Apple identity token rejected', ['error' => $e->getMessage()]);

                return null;
            }
        }

        return null;
    }

    /**
     * @return array<string, \Firebase\JWT\Key>|null
     */
    private function applePublicKeys(bool $useCache): ?array
    {
        if (! $useCache) {
            Cache::forget(self::KEYS_CACHE_KEY);
        }

        $jwks = Cache::remember(self::KEYS_CACHE_KEY, self::KEYS_CACHE_SECONDS, function () {
            // Sans délai, un incident chez Apple immobilise un processus PHP.
            $response = Http::timeout(10)
                ->retry(1, 200)
                ->get(config('services.apple.keys_url'));

            return $response->successful() ? $response->json() : null;
        });

        if (! is_array($jwks) || empty($jwks['keys'])) {
            // Ne pas garder un échec en cache pendant 24 h.
            Cache::forget(self::KEYS_CACHE_KEY);

            return null;
        }

        try {
            return JWK::parseKeySet($jwks);
        } catch (\Throwable $e) {
            Cache::forget(self::KEYS_CACHE_KEY);
            Log::warning('Apple JWKS unparsable', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Retourne la raison du refus, ou null si le jeton est acceptable.
     *
     * La signature et l'expiration sont déjà validées par JWT::decode.
     *
     * @param  array<string, mixed>  $claims
     */
    private function rejectionReason(array $claims): ?string
    {
        if (empty($claims['sub'])) {
            return 'claims incomplets';
        }

        if (($claims['iss'] ?? '') !== self::VALID_ISSUER) {
            return 'émetteur inattendu';
        }

        // Le cœur du contrôle : le jeton doit avoir été émis POUR AlBabor.
        $allowed = $this->allowedClientIds();

        if ($allowed === []) {
            return 'aucun identifiant client configuré';
        }

        if (! in_array($claims['aud'] ?? '', $allowed, true)) {
            return 'destinataire (aud) non autorisé';
        }

        // Absente aux connexions suivantes : on ne contrôle la vérification que
        // si Apple a effectivement transmis une adresse.
        if (isset($claims['email'])) {
            $verified = $claims['email_verified'] ?? null;

            if ($verified !== true && $verified !== 'true') {
                return 'adresse non vérifiée par Apple';
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function allowedClientIds(): array
    {
        $configured = config('services.apple.allowed_client_ids', []);

        if (is_string($configured)) {
            $configured = explode(',', $configured);
        }

        return array_values(array_filter(array_map('trim', $configured)));
    }
}
