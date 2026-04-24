<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\AlgerianPhoneNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Adresse e-mail ou mot de passe incorrect.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->isBlocked()) {
            Auth::logout();

            return response()->json([
                'message' => 'Votre compte a été bloqué. Veuillez contacter l\'administration.',
            ], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        $user->load(['activeSubscription.plan', 'latestVerificationRequest']);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', new AlgerianPhoneNumber],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => AlgerianPhoneNumber::normalize($validated['phone']),
            'password' => Hash::make($validated['password']),
            'account_type' => 'user',
        ]);

        event(new Registered($user));

        $token = $user->createToken('mobile')->plainTextToken;

        $user->load(['activeSubscription.plan', 'latestVerificationRequest']);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.',
            ]);
        }

        return response()->json([
            'message' => 'Impossible d\'envoyer le lien de réinitialisation.',
            'errors' => ['email' => [__($status)]],
        ], 422);
    }
}
