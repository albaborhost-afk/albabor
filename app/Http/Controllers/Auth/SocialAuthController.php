<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Erreur de connexion avec Google. Veuillez reessayer.');
        }

        // Check if user already exists with this google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            if ($user->isBlocked()) {
                return redirect()->route('login')->withErrors(['email' => 'Votre compte a ete bloque.']);
            }

            Auth::login($user, true);
            return redirect()->intended(route('home'));
        }

        // Check if user exists with same email
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link Google account to existing user
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            if ($user->isBlocked()) {
                return redirect()->route('login')->withErrors(['email' => 'Votre compte a ete bloque.']);
            }

            Auth::login($user, true);
            return redirect()->intended(route('home'));
        }

        // Create new user
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'phone' => null,
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => bcrypt(Str::random(24)),
            'account_type' => 'user',
            'verification_status' => 'none',
        ]);

        Auth::login($user, true);
        return redirect()->route('home');
    }
}
