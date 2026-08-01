<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Deuxième moitié de la réinitialisation de mot de passe : elle n'existait pas.
 *
 * La notification de Laravel construit son lien avec `route('password.reset')`.
 * Cette route n'étant déclarée nulle part, l'envoi levait une
 * RouteNotFoundException — la demande de réinitialisation renvoyait donc une
 * erreur 500, et aucun formulaire n'aurait de toute façon accepté le jeton.
 * Concrètement : qui oubliait son mot de passe perdait son compte.
 */
class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                // Quiconque avait déjà une session — navigateur ou application —
                // la perd. Sans cela, réinitialiser son mot de passe après un
                // vol de compte ne mettrait pas l'intrus dehors.
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
