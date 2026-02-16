<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ListingController extends Controller
{
    /**
     * Parcourir les annonces actives avec filtres.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Listing::query()
            ->with(['user', 'media'])
            ->active();

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filtre par wilaya
        if ($request->filled('wilaya')) {
            $query->byWilaya($request->wilaya);
        }

        // Filtre par état
        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        // Filtre par type d'offre
        if ($request->filled('type_offre')) {
            $query->where('type_offre', $request->type_offre);
        }

        // Filtre par prix minimum
        if ($request->filled('price_min')) {
            $query->where('price_dzd', '>=', $request->price_min);
        }

        // Filtre par prix maximum
        if ($request->filled('price_max')) {
            $query->where('price_dzd', '<=', $request->price_max);
        }

        // Filtre par devise
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Recherche textuelle
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Tri
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price_dzd', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_dzd', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Annonces mises en avant en premier
        $query->orderByDesc('featured_until');

        $listings = $query->paginate(20);

        // Ajouter is_favorited si l'utilisateur est authentifié
        if ($user = $request->user()) {
            $favoriteIds = $user->favorites()->pluck('listing_id')->toArray();
            $listings->getCollection()->transform(function ($listing) use ($favoriteIds) {
                $listing->is_favorited = in_array($listing->id, $favoriteIds);
                return $listing;
            });
        }

        return response()->json($listings);
    }

    /**
     * Annonces mises en avant.
     */
    public function featured(): JsonResponse
    {
        $listings = Listing::query()
            ->with(['user', 'media'])
            ->active()
            ->where('featured_until', '>', now())
            ->orderByDesc('featured_until')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $listings,
        ]);
    }

    /**
     * Afficher une annonce.
     */
    public function show(Request $request, Listing $listing): JsonResponse
    {
        // Seules les annonces actives sont visibles aux non-propriétaires
        if ($listing->status !== 'active') {
            if (!$request->user() || $request->user()->id !== $listing->user_id) {
                return response()->json([
                    'message' => 'Annonce introuvable.',
                ], 404);
            }
        }

        $listing->load(['user', 'media']);

        // Enregistrer la vue (unique par jour par IP)
        ListingView::recordView($listing, $request->user(), $request->ip());

        // Annonces similaires (même catégorie)
        $relatedListings = Listing::query()
            ->with(['media'])
            ->active()
            ->where('id', '!=', $listing->id)
            ->where('category', $listing->category)
            ->limit(4)
            ->get();

        // Ajouter is_favorited si l'utilisateur est authentifié
        $isFavorited = false;
        if ($user = $request->user()) {
            $isFavorited = $user->hasFavorited($listing);
        }

        return response()->json([
            'listing' => $listing,
            'is_favorited' => $isFavorited,
            'related_listings' => $relatedListings,
        ]);
    }

    /**
     * Mes annonces (utilisateur authentifié).
     */
    public function myListings(Request $request): JsonResponse
    {
        $listings = $request->user()
            ->listings()
            ->with('media')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($listings);
    }

    /**
     * Créer une annonce.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier les permissions de catégorie (moteur/pièces nécessitent un abonnement)
        $category = $request->category;
        if (in_array($category, ['engine', 'parts'])) {
            if (!$user->canPublishEngineOrParts()) {
                return response()->json([
                    'message' => 'Un abonnement vendeur actif est requis pour publier dans cette catégorie.',
                    'errors' => [
                        'category' => ['Un abonnement vendeur actif est requis pour publier des moteurs ou pièces détachées.'],
                    ],
                ], 403);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|in:boat,jetski,engine,parts',
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'type_offre' => 'required|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'required|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => 'nullable|string|max:20',
            'numero_mobile' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Créer l'annonce
        $listing = Listing::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'type_offre' => $validated['type_offre'],
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'],
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? 'Algérie',
            'numero_whatsapp' => $validated['numero_whatsapp'] ?? null,
            'numero_mobile' => $validated['numero_mobile'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
            'status' => 'awaiting_payment',
        ]);

        // Gérer les images
        $this->handleImageUpload($listing, $request->file('images'));

        // Prix de publication
        $amount = $this->getPublishPrice($listing->category);

        $listing->load(['user', 'media']);

        return response()->json([
            'message' => 'Annonce créée avec succès. Un paiement est requis pour la publier.',
            'listing' => $listing,
            'publish_price' => $amount,
        ], 201);
    }

    /**
     * Mettre à jour une annonce.
     */
    public function update(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $user = $request->user();

        // Vérifier le changement de catégorie vers moteur/pièces
        $newCategory = $request->input('category', $listing->category);
        if (in_array($newCategory, ['engine', 'parts']) && !in_array($listing->category, ['engine', 'parts'])) {
            if (!$user->canPublishEngineOrParts()) {
                return response()->json([
                    'message' => 'Un abonnement vendeur actif est requis pour publier dans cette catégorie.',
                    'errors' => [
                        'category' => ['Un abonnement vendeur actif est requis pour publier des moteurs ou pièces détachées.'],
                    ],
                ], 403);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|in:boat,jetski,engine,parts',
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'type_offre' => 'required|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'required|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => 'nullable|string|max:20',
            'numero_mobile' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'delete_images' => 'nullable|array',
        ]);

        $listing->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'type_offre' => $validated['type_offre'],
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'],
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? 'Algérie',
            'numero_whatsapp' => $validated['numero_whatsapp'] ?? null,
            'numero_mobile' => $validated['numero_mobile'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
        ]);

        // Supprimer les images sélectionnées
        if (!empty($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $mediaId) {
                $media = $listing->media()->find($mediaId);
                if ($media) {
                    Storage::disk('public')->delete($media->path);
                    if ($media->thumbnail_path) {
                        Storage::disk('public')->delete($media->thumbnail_path);
                    }
                    $media->delete();
                }
            }
        }

        // Ajouter de nouvelles images (max 10 au total)
        if ($request->hasFile('new_images')) {
            $currentCount = $listing->media()->count();
            $maxNew = 10 - $currentCount;

            if ($maxNew <= 0) {
                return response()->json([
                    'message' => 'Nombre maximum d\'images atteint (10). Supprimez des images avant d\'en ajouter.',
                    'listing' => $listing->load(['user', 'media']),
                ], 422);
            }

            $newImages = array_slice($request->file('new_images'), 0, $maxNew);
            $this->handleImageUpload($listing, $newImages);
        }

        $listing->load(['user', 'media']);

        return response()->json([
            'message' => 'Annonce mise à jour avec succès.',
            'listing' => $listing,
        ]);
    }

    /**
     * Supprimer une annonce.
     */
    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        // Empêcher la suppression si des paiements sont en attente
        if ($listing->payments()->where('status', 'pending')->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette annonce car elle a des paiements en attente.',
            ], 409);
        }

        // Supprimer tous les fichiers médias du stockage
        foreach ($listing->media as $media) {
            Storage::disk('public')->delete($media->path);
            if ($media->thumbnail_path) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }
        }

        $listing->delete();

        return response()->json([
            'message' => 'Annonce supprimée avec succès.',
        ]);
    }

    /**
     * Marquer une annonce comme vendue.
     */
    public function markAsSold(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $listing->update(['status' => 'sold']);

        return response()->json([
            'message' => 'Annonce marquée comme vendue.',
            'listing' => $listing,
        ]);
    }

    /**
     * Mettre en pause une annonce.
     */
    public function pause(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return response()->json([
                'message' => 'Seule une annonce active peut être mise en pause.',
            ], 422);
        }

        $listing->update(['status' => 'paused']);

        return response()->json([
            'message' => 'Annonce mise en pause.',
            'listing' => $listing,
        ]);
    }

    /**
     * Réactiver une annonce en pause.
     */
    public function reactivate(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'paused') {
            return response()->json([
                'message' => 'Seule une annonce en pause peut être réactivée.',
            ], 422);
        }

        $listing->update(['status' => 'active']);

        return response()->json([
            'message' => 'Annonce réactivée avec succès.',
            'listing' => $listing,
        ]);
    }

    /**
     * Profil vendeur : informations utilisateur + ses annonces actives.
     */
    public function vendorProfile(User $user): JsonResponse
    {
        $listings = $user->listings()
            ->with('media')
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_picture_url' => $user->profile_picture_url,
                'account_type' => $user->account_type,
                'verified_badge' => $user->verified_badge,
                'created_at' => $user->created_at,
            ],
            'listings' => $listings,
        ]);
    }

    /**
     * Gérer l'upload et le redimensionnement des images.
     */
    protected function handleImageUpload(Listing $listing, array $images): void
    {
        $order = $listing->media()->max('order') ?? 0;

        foreach ($images as $image) {
            $order++;

            // Générer un nom de fichier unique
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'listings/' . $listing->id . '/' . $filename;
            $thumbPath = 'listings/' . $listing->id . '/thumb_' . $filename;

            // Redimensionner et sauvegarder l'image principale (max 1200px)
            $img = Image::read($image);
            $img->scaleDown(1200, 1200);
            Storage::disk('public')->put($path, $img->toJpeg(85));

            // Créer la miniature (300px)
            $thumb = Image::read($image);
            $thumb->cover(300, 300);
            Storage::disk('public')->put($thumbPath, $thumb->toJpeg(75));

            ListingMedia::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'thumbnail_path' => $thumbPath,
                'order' => $order,
            ]);
        }
    }

    /**
     * Obtenir le prix de publication selon la catégorie.
     */
    protected function getPublishPrice(string $category): int
    {
        return in_array($category, ['boat', 'jetski']) ? 5000 : 0;
    }
}
