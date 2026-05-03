<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingMedia;
use App\Rules\AlgerianPhoneNumber;
use App\Services\ListingImageWatermark;
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

        // Filtre par type (ex: type de bateau)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        // Filtres avancés sur les spécifications (JSON)
        if ($request->filled('fabricant')) {
            $query->whereRaw("json_extract(specs, '$.general.fabricant') LIKE ?", ['%' . $request->fabricant . '%']);
        }
        if ($request->filled('year_min')) {
            $query->whereRaw("json_extract(specs, '$.general.annee_construction') >= ?", [(int) $request->year_min]);
        }
        if ($request->filled('year_max')) {
            $query->whereRaw("json_extract(specs, '$.general.annee_construction') <= ?", [(int) $request->year_max]);
        }
        if ($request->filled('length_min')) {
            $query->whereRaw("json_extract(specs, '$.dimensions.longueur') >= ?", [(float) $request->length_min]);
        }
        if ($request->filled('length_max')) {
            $query->whereRaw("json_extract(specs, '$.dimensions.longueur') <= ?", [(float) $request->length_max]);
        }
        if ($request->filled('power_min')) {
            $query->whereRaw("json_extract(specs, '$.motorisation.puissance_totale') >= ?", [(int) $request->power_min]);
        }
        if ($request->filled('power_max')) {
            $query->whereRaw("json_extract(specs, '$.motorisation.puissance_totale') <= ?", [(int) $request->power_max]);
        }
        if ($request->filled('engine_brand')) {
            $query->whereRaw("json_extract(specs, '$.motorisation.marque_moteur') LIKE ?", ['%' . $request->engine_brand . '%']);
        }
        if ($request->filled('cabins_min')) {
            $query->whereRaw("json_extract(specs, '$.amenagements.nombre_cabines') >= ?", [(int) $request->cabins_min]);
        }
        if ($request->filled('berths_min')) {
            $query->whereRaw("json_extract(specs, '$.amenagements.nombre_couchettes') >= ?", [(int) $request->berths_min]);
        }

        // Annonces mises en avant en premier (tri primaire)
        $query->orderByRaw("CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 1 ELSE 0 END DESC", [now()]);

        // Tri secondaire
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
                $query->orderByRaw('COALESCE(last_renewed_at, created_at) DESC');
        }

        $listings = $query->paginate(20);

        // Ajouter is_favorited si l'utilisateur est authentifié
        if ($user = $request->user()) {
            $favoriteIds = $user->favorites()->pluck('listing_id')->toArray();
            $listings->getCollection()->transform(function ($listing) use ($favoriteIds) {
                $listing->is_favorited = in_array($listing->id, $favoriteIds);
                return $listing;
            });
        }

        // Hide seller contact info (guests always; authenticated per mediation rules)
        $requestUser = $request->user();
        $listings->getCollection()->transform(function ($listing) use ($requestUser) {
            return $listing->applyContactVisibility($requestUser);
        });

        return response()->json($listings);
    }

    /**
     * Annonces mises en avant.
     */
    public function featured(Request $request): JsonResponse
    {
        $listings = Listing::query()
            ->with(['user', 'media'])
            ->active()
            ->where('featured_until', '>', now())
            ->orderByDesc('featured_until')
            ->limit(10)
            ->get();

        // Hide seller contact info (guests always; authenticated per mediation rules)
        $requestUser = $request->user();
        $listings->transform(function ($listing) use ($requestUser) {
            return $listing->applyContactVisibility($requestUser);
        });

        return response()->json([
            'data' => $listings,
        ]);
    }

    /**
     * Afficher une annonce.
     */
    public function show(Request $request, Listing $listing): JsonResponse
    {
        // Seules les annonces actives sont visibles aux non-propriétaires (sauf admins)
        if ($listing->status !== 'active') {
            $user = $request->user();
            if (!$user || ($user->id !== $listing->user_id && !$user->isAdmin())) {
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

        // Hide seller contact info (guests always; authenticated per mediation rules)
        $requestUser = $request->user();
        $listing->applyContactVisibility($requestUser);
        $relatedListings->transform(fn ($r) => $r->applyContactVisibility($requestUser));

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
            ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
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
            'type' => ['nullable', 'string', function ($attribute, $value, $fail) use ($request) {
                $category = $request->input('category');
                $allowed = Listing::CATEGORY_TYPES[$category] ?? null;
                if ($allowed === null) return; // type only applies when the category has types
                if (empty($value)) {
                    $fail('Le type est requis pour cette catégorie.');
                } elseif (!array_key_exists($value, $allowed)) {
                    $fail('Le type sélectionné n\'est pas valide.');
                }
            }],
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'price_display_unit' => 'nullable|in:milliard,million',
            'type_offre' => 'nullable|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => ['nullable', 'string', AlgerianPhoneNumber::nullable()],
            'numero_mobile' => ['nullable', 'string', AlgerianPhoneNumber::nullable()],
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'images' => 'required|array|min:1|max:' . Listing::MAX_IMAGES,
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,heic,heif|max:' . Listing::MAX_IMAGE_SIZE_KB,
            'video_url' => 'nullable|url|max:500',
        ]);

        if (($validated['currency'] ?? null) !== 'DZD') {
            $validated['price_display_unit'] = null;
        }

        // Strip type for categories that don't use types
        if (!isset(Listing::CATEGORY_TYPES[$validated['category'] ?? ''])) {
            $validated['type'] = null;
        }

        // Créer l'annonce
        $listing = Listing::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'type' => $validated['type'] ?? null,
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'price_display_unit' => $validated['price_display_unit'] ?? null,
            'type_offre' => $validated['type_offre'],
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => AlgerianPhoneNumber::normalize($validated['numero_whatsapp'] ?? null),
            'numero_mobile' => AlgerianPhoneNumber::normalize($validated['numero_mobile'] ?? null),
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
            'status' => 'awaiting_payment',
        ] + (\Schema::hasColumn('listings', 'video_url') ? ['video_url' => $request->video_url] : []));

        // Gérer les images
        $savedCount = $this->handleImageUpload($listing, $request->file('images'));

        if ($savedCount === 0) {
            $listing->delete();

            return response()->json([
                'message' => 'Impossible de traiter les images. Veuillez réessayer avec d\'autres fichiers.',
            ], 422);
        }

        // Publication gratuite pour les utilisateurs autorisés
        if ($user->hasFreePublishing()) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            $listing->load(['user', 'media']);

            return response()->json([
                'message' => 'Votre annonce a été créée et sera examinée par notre équipe.',
                'listing' => $listing,
                'publish_price' => 0,
                'is_first_listing' => false,
                'free_publishing' => true,
            ], 201);
        }

        if (in_array($listing->category, ['engine', 'parts']) && $user->canPublishEngineOrParts()) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            $listing->load(['user', 'media']);

            return response()->json([
                'message' => 'Votre annonce vendeur a été créée et sera examinée par notre équipe.',
                'listing' => $listing,
                'publish_price' => 0,
                'is_first_listing' => false,
                'vendor_subscription_applied' => true,
            ], 201);
        }

        // Première annonce gratuite
        $isFirstListing = Listing::where('user_id', $user->id)
            ->where('id', '!=', $listing->id)
            ->count() === 0;

        if ($isFirstListing) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            $listing->load(['user', 'media']);

            return response()->json([
                'message' => 'Félicitations ! Votre première annonce est gratuite et sera examinée par notre équipe.',
                'listing' => $listing,
                'publish_price' => 0,
                'is_first_listing' => true,
            ], 201);
        }

        // Prix de publication
        $amount = $this->getPublishPrice($listing->category);

        $listing->load(['user', 'media']);

        return response()->json([
            'message' => 'Annonce créée avec succès. Un paiement est requis pour la publier.',
            'listing' => $listing,
            'publish_price' => $amount,
            'is_first_listing' => false,
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
            'type' => ['nullable', 'string', function ($attribute, $value, $fail) use ($request) {
                $category = $request->input('category');
                $allowed = Listing::CATEGORY_TYPES[$category] ?? null;
                if ($allowed === null) return; // type only applies when the category has types
                if (empty($value)) {
                    $fail('Le type est requis pour cette catégorie.');
                } elseif (!array_key_exists($value, $allowed)) {
                    $fail('Le type sélectionné n\'est pas valide.');
                }
            }],
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'price_display_unit' => 'nullable|in:milliard,million',
            'type_offre' => 'nullable|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => ['nullable', 'string', AlgerianPhoneNumber::nullable()],
            'numero_mobile' => ['nullable', 'string', AlgerianPhoneNumber::nullable()],
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'new_images' => 'nullable|array|max:' . Listing::MAX_IMAGES,
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp,heic,heif|max:' . Listing::MAX_IMAGE_SIZE_KB,
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:listing_media,id',
            'video_url' => 'nullable|url|max:500',
        ]);

        if (($validated['currency'] ?? null) !== 'DZD') {
            $validated['price_display_unit'] = null;
        }

        // Strip type for categories that don't use types
        if (!isset(Listing::CATEGORY_TYPES[$validated['category'] ?? ''])) {
            $validated['type'] = null;
        }

        $listing->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'type' => $validated['type'] ?? null,
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'price_display_unit' => $validated['price_display_unit'] ?? null,
            'type_offre' => $validated['type_offre'],
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => AlgerianPhoneNumber::normalize($validated['numero_whatsapp'] ?? null),
            'numero_mobile' => AlgerianPhoneNumber::normalize($validated['numero_mobile'] ?? null),
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
        ] + (\Schema::hasColumn('listings', 'video_url') ? ['video_url' => $request->video_url] : []));

        // Supprimer les images sélectionnées
        if (!empty($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $mediaId) {
                $media = $listing->media()->find($mediaId);
                if ($media) {
                    Storage::disk($this->listingDisk())->delete($media->path);
                    if ($media->thumbnail_path) {
                        Storage::disk($this->listingDisk())->delete($media->thumbnail_path);
                    }
                    $media->delete();
                }
            }
        }

        // Ajouter de nouvelles images (max 20 au total)
        if ($request->hasFile('new_images')) {
            $currentCount = $listing->media()->count();
            $maxNew = Listing::MAX_IMAGES - $currentCount;

            if ($maxNew <= 0) {
                return response()->json([
                    'message' => 'Nombre maximum d\'images atteint (' . Listing::MAX_IMAGES . '). Supprimez des images avant d\'en ajouter.',
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
            Storage::disk($this->listingDisk())->delete($media->path);
            if ($media->thumbnail_path) {
                Storage::disk($this->listingDisk())->delete($media->thumbnail_path);
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
     * Remonter une annonce en haut de la liste (renouvellement).
     * Limite : 1 fois par 7 jours par annonce.
     */
    public function renew(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return response()->json([
                'message' => 'Seules les annonces actives peuvent être remontées.',
            ], 422);
        }

        if (!$listing->canRenew()) {
            $nextRenewal = $listing->last_renewed_at->copy()->addDays(7);
            $daysLeft = max(1, (int) ceil(now()->diffInHours($nextRenewal, false) / 24));
            return response()->json([
                'message' => "Renouvellement disponible dans {$daysLeft} jour(s).",
                'next_renewal_at' => $nextRenewal->toISOString(),
                'can_renew_in_days' => $daysLeft,
            ], 429);
        }

        $listing->update(['last_renewed_at' => now()]);

        $nextRenewal = now()->addDays(7);

        return response()->json([
            'message' => 'Annonce remontée avec succès.',
            'next_renewal_at' => $nextRenewal->toISOString(),
        ]);
    }

    /**
     * Profil vendeur : informations utilisateur + ses annonces actives.
     */
    public function vendorProfile(Request $request, User $user): JsonResponse
    {
        $listings = $user->listings()
            ->with('media')
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Hide seller contact info (guests always; authenticated per mediation rules)
        $requestUser = $request->user();
        $listings->getCollection()->transform(function ($listing) use ($requestUser) {
            return $listing->applyContactVisibility($requestUser);
        });

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
     * Get the configured disk name for listing media storage.
     */
    protected function listingDisk(): string
    {
        return config('filesystems.listing_disk', 'public');
    }

    /**
     * Gérer l'upload et le redimensionnement des images.
     */
    protected function handleImageUpload(Listing $listing, array $images): int
    {
        $order = $listing->media()->max('order') ?? 0;
        $savedCount = 0;
        $disk = $this->listingDisk();

        foreach ($images as $image) {
            try {
                $order++;

                // Générer un nom de fichier unique
                $filename = uniqid('img_', true) . '.jpg';
                $path = 'listings/' . $listing->id . '/' . $filename;
                $thumbPath = 'listings/' . $listing->id . '/thumb_' . $filename;

                // Redimensionner, watermark Albabor, puis sauvegarder l'image principale (max 1200px)
                $img = Image::read($image);
                $img->scaleDown(1200, 1200);
                app(ListingImageWatermark::class)->apply($img);
                Storage::disk($disk)->put($path, $img->toJpeg(85));

                // Créer la miniature (300px) avec watermark
                $thumb = Image::read($image);
                $thumb->cover(300, 300);
                app(ListingImageWatermark::class)->apply($thumb);
                Storage::disk($disk)->put($thumbPath, $thumb->toJpeg(75));

                ListingMedia::create([
                    'listing_id' => $listing->id,
                    'path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'order' => $order,
                ]);

                $savedCount++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $savedCount;
    }

    /**
     * Obtenir le prix de publication selon la catégorie.
     */
    protected function getPublishPrice(string $category): int
    {
        return in_array($category, ['boat', 'jetski']) ? 5000 : 0;
    }
}
