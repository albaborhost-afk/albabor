<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Profil public d'un vendeur : toutes ses annonces actives au même endroit.
 *
 * Un même vendeur publie souvent plusieurs bateaux, ou des dizaines de pièces.
 * Depuis une annonce, l'acheteur doit pouvoir ouvrir le vendeur et parcourir
 * le reste de son stock — c'est la boutique du particulier, l'équivalent de
 * ce que /boutique offre déjà aux vendeurs professionnels.
 *
 * La page ne révèle rien de plus qu'une annonce : ni e-mail, ni téléphone.
 * Un vendeur qui publie sous « Invité » y apparaît anonymisé comme ailleurs,
 * le masquage étant appliqué par le modèle User.
 */
class SellerProfileController extends Controller
{
    public function show(User $user)
    {
        // Un compte bloqué n'a plus de vitrine ; l'administration n'en a pas.
        abort_if($user->isBlocked() || $user->isAdmin(), 404);

        $listings = $user->listings()
            ->active()
            ->with('media')
            ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
            ->paginate(12);

        $stats = [
            'active_listings' => $listings->total(),
            'total_views'     => (int) $user->listings()->active()->sum('views_count'),
        ];

        // Les vendeurs professionnels ont une vitrine dédiée : on y renvoie.
        $boutique = $user->vendorProfile()->where('is_active', true)->first();

        return view('sellers.show', compact('user', 'listings', 'stats', 'boutique'));
    }
}
