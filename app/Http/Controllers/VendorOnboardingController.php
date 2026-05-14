<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Onboarding "Espace Vendeur" — devenir vendeur de pièces / moteurs + créer sa boutique.
 */
class VendorOnboardingController extends Controller
{
    /** Landing publique : argumentaire "Devenir vendeur de pièces". */
    public function landing()
    {
        $hasShop = Auth::check() && Auth::user()->hasVendorProfile();

        return view('vendor.onboarding.landing', [
            'hasShop' => $hasShop,
        ]);
    }

    /** Formulaire de création de boutique (auth requise). */
    public function create()
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour créer votre boutique.');
        }

        if (Auth::user()->hasVendorProfile()) {
            return redirect(url('/vendeur'))
                ->with('info', 'Vous disposez déjà d\'une boutique vendeur.');
        }

        return view('vendor.onboarding.create');
    }

    /** Traite la création de la boutique + bascule le compte en vendor. */
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour créer votre boutique.');
        }

        $user = Auth::user();

        $validated = $request->validate([
            'shop_name'     => 'required|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'wilaya'        => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:500',
            'logo'          => 'nullable|image|max:15360',
        ], [
            'shop_name.required' => 'Le nom de la boutique est obligatoire.',
            'contact_email.email' => 'L\'adresse e-mail de contact n\'est pas valide.',
            'logo.image'         => 'Le logo doit être une image.',
            'logo.max'           => 'Le logo ne doit pas dépasser 15 Mo.',
        ]);

        // 1. L'utilisateur a déjà une boutique.
        if ($user->hasVendorProfile()) {
            return back()
                ->withInput()
                ->with('error', 'Vous disposez déjà d\'une boutique vendeur.');
        }

        // 2. Upload du logo (optionnel).
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $disk = config('filesystems.listing_disk', 'public');
            $path = 'vendor-logos/' . Str::uuid()->toString() . '.jpg';

            $img = Image::read($request->file('logo'));
            $img->scaleDown(600, 600);
            Storage::disk($disk)->put($path, (string) $img->toJpeg(85));

            $logoPath = $path;
        }

        // 3. Création du profil vendeur (slug auto-généré par le modèle).
        VendorProfile::create([
            'user_id'       => $user->id,
            'shop_name'     => $validated['shop_name'],
            'slug'          => '',
            'description'   => $validated['description'] ?? null,
            'logo_path'     => $logoPath,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'wilaya'        => $validated['wilaya'] ?? null,
            'address'       => $validated['address'] ?? null,
            'is_active'     => true,
        ]);

        // 4. Bascule le compte en vendeur.
        $user->update(['account_type' => 'vendor']);

        // 5. Redirection vers l'espace vendeur Filament.
        return redirect(url('/vendeur'))
            ->with('success', 'Votre boutique a été créée ! Bienvenue dans votre espace vendeur.');
    }
}
