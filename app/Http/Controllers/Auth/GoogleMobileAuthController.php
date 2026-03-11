<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleMobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        // Verify token with Google
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->id_token,
        ]);

        if (!$response->successful()) {
            return response()->json(['message' => 'Token Google invalide'], 401);
        }

        $googleUser = $response->json();

        // Check for error in response
        if (isset($googleUser['error'])) {
            return response()->json(['message' => 'Token Google invalide'], 401);
        }

        $googleId  = $googleUser['sub']     ?? null;
        $email     = $googleUser['email']   ?? null;
        $name      = $googleUser['name']    ?? 'Utilisateur';
        $avatar    = $googleUser['picture'] ?? null;

        if (!$googleId || !$email) {
            return response()->json(['message' => 'Informations Google incomplètes'], 422);
        }

        // Find existing user by google_id
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            // Find by email and link Google account
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['google_id' => $googleId, 'avatar' => $avatar ?? $user->avatar]);
            } else {
                // Create new user
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

        if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
            return response()->json(['message' => 'Votre compte a été bloqué.'], 403);
        }

        $token = $user->createToken('mobile-google')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }
}
