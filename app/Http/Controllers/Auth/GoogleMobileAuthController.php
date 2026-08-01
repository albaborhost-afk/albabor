<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Connexion Google depuis les applications mobiles.
 *
 * Un id_token n'est une preuve d'identité que si l'on vérifie POUR QUI il a
 * été émis. Sans cela, n'importe quelle application Google pouvait fournir un
 * jeton que ce point d'entrée acceptait, et le compte AlBabor correspondant à
 * l'adresse était remis à l'appelant.
 */
class GoogleMobileAuthController extends Controller
{
    /** Émetteurs légitimes d'un id_token Google. */
    private const VALID_ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    public function login(Request $request): JsonResponse
    {
        $request->validate(['id_token' => 'required|string']);

        // Sans délai, un incident chez Google immobilise un processus PHP.
        $response = Http::timeout(10)
            ->retry(1, 200)
            ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $request->id_token]);

        if (! $response->successful()) {
            return response()->json(['message' => 'Token Google invalide'], 401);
        }

        $claims = $response->json();

        if (! is_array($claims) || isset($claims['error'])) {
            return response()->json(['message' => 'Token Google invalide'], 401);
        }

        if ($rejection = $this->rejectionReason($claims)) {
            Log::warning('Google sign-in refused', [
                'reason' => $rejection,
                'aud'    => $claims['aud'] ?? null,
                'iss'    => $claims['iss'] ?? null,
            ]);

            return response()->json(['message' => 'Token Google invalide'], 401);
        }

        $googleId = $claims['sub'];
        $email     = strtolower(trim($claims['email']));
        $name      = $claims['name']    ?? 'Utilisateur';
        $avatar    = $claims['picture'] ?? null;

        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            // Rattachement par adresse : n'est légitime que parce que
            // `email_verified` a été contrôlé au-dessus. Sans cela, il suffisait
            // de créer un compte Google avec l'adresse d'un vendeur pour
            // récupérer son compte AlBabor.
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'avatar'    => $avatar ?? $user->getRawOriginal('avatar'),
                ]);
            } else {
                $user = User::create([
                    'name'                => $name,
                    'email'               => $email,
                    'phone'               => null,
                    'google_id'           => $googleId,
                    'avatar'              => $avatar,
                    'password'            => bcrypt(Str::random(32)),
                    'account_type'        => 'user',
                    'verification_status' => 'none',
                ]);
            }
        }

        if ($user->isBlocked()) {
            return response()->json(['message' => 'Votre compte a été bloqué.'], 403);
        }

        $token = $user->createToken('mobile-google')->plainTextToken;

        return response()->json([
            'token' => $token,
            // Son propre compte : il doit voir son vrai nom, pas « Invité ».
            'user'  => $user->withRealName(),
        ]);
    }

    /**
     * Retourne la raison du refus, ou null si le jeton est acceptable.
     */
    private function rejectionReason(array $claims): ?string
    {
        if (empty($claims['sub']) || empty($claims['email'])) {
            return 'claims incomplets';
        }

        if (! in_array($claims['iss'] ?? '', self::VALID_ISSUERS, true)) {
            return 'émetteur inattendu';
        }

        // Le cœur du contrôle : le jeton doit avoir été émis POUR AlBabor.
        // Un jeton destiné à une autre application ne prouve rien ici.
        $allowed = $this->allowedClientIds();

        if ($allowed === []) {
            return 'aucun identifiant client configuré';
        }

        if (! in_array($claims['aud'] ?? '', $allowed, true)) {
            return 'destinataire (aud) non autorisé';
        }

        // Google renvoie la chaîne « true » / « false » sur cet endpoint.
        $verified = $claims['email_verified'] ?? null;

        if ($verified !== true && $verified !== 'true') {
            return 'adresse non vérifiée par Google';
        }

        return null;
    }

    /** @return array<int, string> */
    private function allowedClientIds(): array
    {
        $configured = config('services.google.allowed_client_ids', []);

        if (is_string($configured)) {
            $configured = explode(',', $configured);
        }

        $configured[] = config('services.google.client_id');

        return array_values(array_filter(array_map('trim', $configured)));
    }
}
