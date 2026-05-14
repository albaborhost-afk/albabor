<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;

/**
 * Vitrines publiques des boutiques de pieces / moteurs.
 */
class BoutiqueController extends Controller
{
    /** Annuaire de toutes les boutiques actives. */
    public function index()
    {
        $boutiques = VendorProfile::where('is_active', true)
            ->with('user')
            ->withCount(['listings' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('shop_name')
            ->paginate(24);

        return view('boutique.index', compact('boutiques'));
    }

    /** Vitrine d'une boutique : profil + annonces pieces / moteurs. */
    public function show(VendorProfile $vendorProfile)
    {
        abort_unless($vendorProfile->is_active, 404);

        $listings = $vendorProfile->listings()
            ->where('status', 'active')
            ->with('media')
            ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
            ->paginate(12);

        return view('boutique.show', compact('vendorProfile', 'listings'));
    }
}
