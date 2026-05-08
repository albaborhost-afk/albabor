<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\InternationalPhoneNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', new InternationalPhoneNumber],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        [$countryCode, $phoneNational] = InternationalPhoneNumber::split($validated['phone']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phoneNational,
            'phone_country_code' => $countryCode,
            'password' => Hash::make($validated['password']),
            'account_type' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }
}
