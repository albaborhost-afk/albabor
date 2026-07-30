<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\ListingView;
use App\Models\Payment;
use App\Rules\AlgerianPhoneNumber;
use App\Rules\InternationalPhoneNumber;
use App\Services\ListingImageWatermark;
use App\Services\ListingMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Setting;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->with(['user', 'media'])
            ->active();

        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Pays (country) filter
        if ($request->filled('wilaya')) {
            $query->where('pays', $request->wilaya);
        }

        // Etat filter
        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        // Type offre filter
        if ($request->filled('type_offre')) {
            $query->where('type_offre', $request->type_offre);
        }

        // Type filter (e.g. boat type)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Price range
        if ($request->filled('price_min')) {
            $query->where('price_dzd', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_dzd', '<=', $request->price_max);
        }

        // Currency filter
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Search
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Advanced spec filters
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

        // Featured listings always first (primary sort)
        $query->orderByRaw("CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 1 ELSE 0 END DESC", [now()]);

        // Secondary sort
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

        $listings = $query->paginate(20)->withQueryString();

        // Get wilayas for filter
        $wilayas = $this->getWilayas();

        return view('listings.index', compact('listings', 'wilayas'));
    }

    public function show(Listing $listing)
    {
        // Only show active listings to non-owners/non-admins
        if ($listing->status !== 'active') {
            $user = Auth::user();
            if (!$user || ($user->id !== $listing->user_id && !$user->isAdmin())) {
                abort(404);
            }
        }

        $listing->load(['user', 'media']);

        // Track view (unique per day per IP)
        $this->trackView($listing);

        // Get related listings
        $relatedListings = Listing::query()
            ->with(['media'])
            ->active()
            ->where('id', '!=', $listing->id)
            ->where('category', $listing->category)
            ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
            ->limit(4)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings'));
    }

    public function create()
    {
        $user = Auth::user();
        $wilayas = $this->getWilayas();

        $exchangeRate = Setting::getExchangeRate();

        $isFirstListing = Listing::where('user_id', $user->id)->count() === 0;
        $hasFreePublishing = $user->hasFreePublishing();
        $canPublishEngineOrParts = $user->canPublishEngineOrParts();

        return view('listings.create', compact('wilayas', 'exchangeRate', 'isFirstListing', 'hasFreePublishing', 'canPublishEngineOrParts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Renvoi du même formulaire (timeout de la passerelle, double clic,
        // reprise après coupure réseau) : l'annonce a déjà été enregistrée, on
        // renvoie sa destination au lieu de créer un doublon.
        $clientToken = $this->normalizeClientToken($request->input('client_token'));

        if ($clientToken !== null) {
            $alreadySubmitted = Listing::where('user_id', $user->id)
                ->where('client_token', $clientToken)
                ->first();

            if ($alreadySubmitted) {
                return $this->listingSubmittedResponse($request, $alreadySubmitted);
            }
        }

        // Validate category permissions
        $category = $request->category;
        if (in_array($category, ['engine', 'parts'])) {
            if (!$user->canPublishEngineOrParts()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => __('messages.subscription_required_for_category'),
                        'errors' => [
                            'category' => [__('messages.subscription_required_for_category')],
                        ],
                    ], 422);
                }

                return back()->withErrors([
                    'category' => __('messages.subscription_required_for_category')
                ]);
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
            'price_dzd' => 'required|numeric|min:0|max:' . $this->maxListingPriceDzd(),
            'currency' => 'required|in:DZD,EUR,OTHER',
            'currency_label' => 'nullable|string|max:10',
            'price_display_unit' => 'nullable|in:milliard,million',
            'type_offre' => 'nullable|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => ['nullable', 'string', InternationalPhoneNumber::nullable()],
            'numero_mobile' => ['nullable', 'string', InternationalPhoneNumber::nullable()],
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'images' => 'required|array|min:1|max:' . Listing::MAX_IMAGES,
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,heic,heif|max:' . Listing::MAX_IMAGE_SIZE_KB,
            'video_url' => 'nullable|url|max:500',
        ]);

        // L'unite d'affichage n'a de sens que pour les listings DZD
        if (($validated['currency'] ?? null) !== 'DZD') {
            $validated['price_display_unit'] = null;
        }

        // Strip type for categories that don't use types
        if (!isset(Listing::CATEGORY_TYPES[$validated['category'] ?? ''])) {
            $validated['type'] = null;
        }

        // Create listing
        $listingData = [
            'user_id' => $user->id,
            'client_token' => $clientToken,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'type' => $validated['type'] ?? null,
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'currency_label' => $validated['currency'] === 'OTHER' ? ($validated['currency_label'] ?? null) : null,
            'price_display_unit' => $validated['price_display_unit'] ?? null,
            'type_offre' => $validated['type_offre'] ?? null,
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => InternationalPhoneNumber::normalize($validated['numero_whatsapp'] ?? null),
            'numero_mobile' => InternationalPhoneNumber::normalize($validated['numero_mobile'] ?? null),
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
            'status' => 'awaiting_payment',
        ];

        // Only include video_url if the column exists (migration may not have run yet)
        if (\Schema::hasColumn('listings', 'video_url')) {
            $listingData['video_url'] = $validated['video_url'] ?? null;
        }

        $listingData = $this->filterListingPayloadForSchema($listingData);

        try {
            $listing = Listing::create($listingData);
        } catch (\Throwable $e) {
            \Log::error('Listing creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'listing_data_keys' => array_keys($listingData),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Une erreur est survenue lors de la création de l\'annonce. Veuillez réessayer.',
                ], 500);
            }

            return back()->withInput()->withErrors(['general' => 'Une erreur est survenue lors de la création de l\'annonce. Veuillez réessayer.']);
        }

        // Handle images
        $savedCount = $this->handleImageUpload($listing, $request->file('images'));

        if ($savedCount === 0) {
            $listing->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Impossible de sauvegarder les images. Veuillez réessayer.',
                    'errors' => [
                        'images' => ['Impossible de sauvegarder les images. Veuillez réessayer.'],
                    ],
                ], 422);
            }

            return back()->withErrors(['images' => 'Impossible de sauvegarder les images. Veuillez réessayer.'])->withInput();
        }

        // Free publishing users skip payment entirely
        if ($user->hasFreePublishing()) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            return $this->listingSubmittedResponse(
                $request,
                $listing,
                __('Votre annonce a été créée et sera examinée par notre équipe.')
            );
        }

        // Vendor subscription covers engine/parts publication without an extra payment step.
        if (in_array($listing->category, ['engine', 'parts']) && $user->canPublishEngineOrParts()) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            return $this->listingSubmittedResponse(
                $request,
                $listing,
                __('Votre annonce vendeur a été créée et sera examinée par notre équipe.')
            );
        }

        // First listing is free — check if user has any other listing
        $isFirstListing = Listing::where('user_id', $user->id)
            ->where('id', '!=', $listing->id)
            ->count() === 0;

        if ($isFirstListing) {
            $listing->update([
                'status'          => 'pending_review',
                'published_until' => now()->addYear(),
            ]);

            return $this->listingSubmittedResponse($request, $listing, __('messages.listing_created_free'));
        }

        return $this->listingSubmittedResponse($request, $listing, __('messages.listing_created_payment_required'));
    }

    /**
     * Le navigateur demande si son envoi a abouti.
     *
     * Une annonce transporte jusqu'à 20 photos : la passerelle peut couper la
     * connexion pendant que le serveur redimensionne, et le navigateur croit à
     * un échec alors que tout est enregistré. Le formulaire interroge alors
     * cette route avec le jeton qu'il a généré avant l'envoi.
     *
     * « pending » ne veut pas dire « échec » : le traitement peut encore être
     * en cours, le client réessaie quelques secondes.
     */
    public function submissionStatus(Request $request)
    {
        $token = $this->normalizeClientToken($request->query('token'));

        if ($token === null) {
            return response()->json(['status' => 'unknown'], 422);
        }

        $listing = Listing::where('user_id', Auth::id())
            ->where('client_token', $token)
            ->first();

        if (! $listing) {
            return response()->json(['status' => 'pending']);
        }

        return response()->json([
            'status'     => 'created',
            'listing_id' => $listing->id,
            'redirect'   => $this->listingSubmissionRedirect($listing),
        ]);
    }

    /**
     * Jeton d'idempotence du formulaire : un UUID généré par le navigateur.
     * Toute autre forme est ignorée (l'envoi reste possible, sans reprise).
     */
    protected function normalizeClientToken(mixed $token): ?string
    {
        $token = is_string($token) ? trim($token) : '';

        return preg_match('/^[A-Za-z0-9-]{8,64}$/', $token) === 1 ? $token : null;
    }

    /**
     * Où atterrit l'utilisateur après un envoi réussi : la page de paiement si
     * l'annonce en attend un, sinon ses annonces.
     */
    protected function listingSubmissionRedirect(Listing $listing): string
    {
        return $listing->status === 'awaiting_payment'
            ? route('listings.payment', $listing)
            : route('listings.my');
    }

    /**
     * Réponse commune à un envoi qui a abouti — que ce soit à la création ou
     * lors d'un renvoi du même jeton après un timeout.
     */
    protected function listingSubmittedResponse(Request $request, Listing $listing, ?string $message = null)
    {
        $awaitingPayment = $listing->status === 'awaiting_payment';
        $redirect        = $this->listingSubmissionRedirect($listing);
        $message       ??= $awaitingPayment
            ? __('messages.listing_created_payment_required')
            : __('Votre annonce a été créée et sera examinée par notre équipe.');

        if ($request->expectsJson()) {
            return response()->json([
                'redirect'   => $redirect,
                'message'    => $message,
                'listing_id' => $listing->id,
            ]);
        }

        $response = redirect()->to($redirect)->with('success', $message);

        return $awaitingPayment ? $response : $response->with('listing_created', true);
    }

    public function edit(Listing $listing)
    {
        $this->authorize('update', $listing);

        $listing->load('media');

        // Clean up orphaned media records (files missing from storage)
        $disk = $this->listingDisk();
        foreach ($listing->media as $media) {
            if (!Storage::disk($disk)->exists($media->path)) {
                \Log::info('Removing orphaned ListingMedia record', [
                    'media_id'   => $media->id,
                    'path'       => $media->path,
                    'listing_id' => $listing->id,
                ]);
                $media->delete();
            }
        }
        $listing->unsetRelation('media')->load('media');

        $wilayas = $this->getWilayas();
        $exchangeRate = Setting::getExchangeRate();

        return view('listings.edit', compact('listing', 'wilayas', 'exchangeRate'));
    }

    public function update(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        $user = Auth::user();

        // Même jeton que l'envoi précédent : le navigateur rejoue un formulaire
        // dont la réponse s'est perdue. Retraiter les photos les dupliquerait.
        $clientToken = $this->normalizeClientToken($request->input('client_token'));

        if ($clientToken !== null && $listing->client_token === $clientToken) {
            return $this->listingUpdatedResponse($request, $listing);
        }

        // Validate category change: if switching to engine/parts, require active subscription
        $newCategory = $request->input('category', $listing->category);
        if (in_array($newCategory, ['engine', 'parts']) && !in_array($listing->category, ['engine', 'parts'])) {
            if (!$user->canPublishEngineOrParts()) {
                return back()->withErrors([
                    'category' => __('messages.subscription_required_for_category')
                ]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|in:boat,jetski,engine,parts',
            'type' => ['nullable', 'string', function ($attribute, $value, $fail) use ($request) {
                $category = $request->input('category');
                $allowed = Listing::CATEGORY_TYPES[$category] ?? null;
                if ($allowed === null) return;
                if (empty($value)) {
                    $fail('Le type est requis pour cette catégorie.');
                } elseif (!array_key_exists($value, $allowed)) {
                    $fail('Le type sélectionné n\'est pas valide.');
                }
            }],
            'price_dzd' => 'required|numeric|min:0|max:' . $this->maxListingPriceDzd(),
            'currency' => 'required|in:DZD,EUR,OTHER',
            'currency_label' => 'nullable|string|max:10',
            'price_display_unit' => 'nullable|in:milliard,million',
            'type_offre' => 'nullable|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
            'visible_a' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:100',
            'numero_whatsapp' => ['nullable', 'string', InternationalPhoneNumber::nullable()],
            'numero_mobile' => ['nullable', 'string', InternationalPhoneNumber::nullable()],
            'contact_email' => 'nullable|email|max:255',
            'specs' => 'nullable|array',
            'mediation_enabled' => 'boolean',
            'new_images' => 'nullable|array|max:' . Listing::MAX_IMAGES,
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp,heic,heif|max:' . Listing::MAX_IMAGE_SIZE_KB,
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:listing_media,id',
            'cover_image_id' => 'nullable|integer|exists:listing_media,id',
            'cover_new_index' => 'nullable|integer|min:0',
            'video_url' => 'nullable|url|max:500',
        ]);

        if (($validated['currency'] ?? null) !== 'DZD') {
            $validated['price_display_unit'] = null;
        }

        // Strip type for categories that don't use types
        if (!isset(Listing::CATEGORY_TYPES[$validated['category'] ?? ''])) {
            $validated['type'] = null;
        }

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'type' => $validated['type'] ?? null,
            'price_dzd' => $validated['price_dzd'],
            'currency' => $validated['currency'],
            'currency_label' => $validated['currency'] === 'OTHER' ? ($validated['currency_label'] ?? null) : null,
            'price_display_unit' => $validated['price_display_unit'] ?? null,
            'type_offre' => $validated['type_offre'] ?? null,
            'etat' => $validated['etat'],
            'remarque_echange' => $validated['remarque_echange'] ?? null,
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => InternationalPhoneNumber::normalize($validated['numero_whatsapp'] ?? null),
            'numero_mobile' => InternationalPhoneNumber::normalize($validated['numero_mobile'] ?? null),
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
            // Enregistré avant le traitement des photos : un renvoi pendant ce
            // traitement retrouve le jeton et n'ajoute pas les mêmes photos.
            'client_token' => $clientToken,
        ];

        // Only include video_url if the column exists
        if (\Schema::hasColumn('listings', 'video_url')) {
            $updateData['video_url'] = $validated['video_url'] ?? null;
        }

        // Re-submit rejected listings for review
        if ($listing->status === 'rejected') {
            $updateData['status'] = 'pending_review';
            $updateData['rejection_reason'] = null;
        }

        $updateData = $this->filterListingPayloadForSchema($updateData);

        $listing->update($updateData);

        // Delete selected images
        if (!empty($validated['delete_images'])) {
            $disk = $this->listingDisk();
            foreach ($validated['delete_images'] as $mediaId) {
                $media = $listing->media()->find($mediaId);
                if ($media) {
                    Storage::disk($disk)->delete($media->path);
                    if ($media->thumbnail_path) {
                        Storage::disk($disk)->delete($media->thumbnail_path);
                    }
                    $media->delete();
                }
            }
        }

        // Add new images
        if ($request->hasFile('new_images')) {
            $currentCount = $listing->media()->count();
            $maxNew = max(0, Listing::MAX_IMAGES - $currentCount);
            \Log::info('Listing image upload', [
                'listing_id'    => $listing->id,
                'disk'          => $this->listingDisk(),
                'existing'      => $currentCount,
                'max_new'       => $maxNew,
                'files_received' => count($request->file('new_images')),
            ]);
            if ($maxNew > 0) {
                $newImages = array_slice($request->file('new_images'), 0, $maxNew);
                $saved = $this->handleImageUpload($listing, $newImages);
                \Log::info('Listing image upload result', [
                    'listing_id' => $listing->id,
                    'saved'      => $saved,
                ]);
            } else {
                \Log::warning('Listing image upload skipped – 20 photo limit reached', [
                    'listing_id' => $listing->id,
                    'existing'   => $currentCount,
                ]);
            }
        } else {
            \Log::info('Listing update – no new_images received', ['listing_id' => $listing->id]);
        }

        // Reorder media: move chosen cover image to position 0
        if (!empty($validated['cover_image_id'])) {
            $coverMedia = $listing->media()->find($validated['cover_image_id']);
            if ($coverMedia) {
                $others = $listing->media()
                    ->where('id', '!=', $coverMedia->id)
                    ->orderBy('order')
                    ->get();
                $coverMedia->update(['order' => 0]);
                foreach ($others as $idx => $m) {
                    $m->update(['order' => $idx + 1]);
                }
            }
        } elseif (isset($validated['cover_new_index']) && $request->hasFile('new_images')) {
            // The user picked one of the freshly uploaded photos as cover.
            // Mutually exclusive with cover_image_id (handled above).
            $newCount  = count($request->file('new_images'));
            $coverIdx  = (int) $validated['cover_new_index'];

            if ($coverIdx >= 0 && $coverIdx < $newCount) {
                // handleImageUpload appended the new media in order, so the most
                // recent $newCount records (sorted by id desc, then reversed)
                // mirror the upload order.
                $freshMedia = $listing->media()
                    ->orderByDesc('id')
                    ->take($newCount)
                    ->get()
                    ->reverse()
                    ->values();

                $coverMedia = $freshMedia->get($coverIdx);
                if ($coverMedia) {
                    $others = $listing->media()
                        ->where('id', '!=', $coverMedia->id)
                        ->orderBy('order')
                        ->get();
                    $coverMedia->update(['order' => 0]);
                    foreach ($others as $idx => $m) {
                        $m->update(['order' => $idx + 1]);
                    }
                }
            }
        }

        return $this->listingUpdatedResponse($request, $listing);
    }

    /**
     * Réponse commune à une modification enregistrée — y compris lorsqu'un
     * renvoi du même jeton court-circuite le retraitement.
     */
    protected function listingUpdatedResponse(Request $request, Listing $listing)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'redirect'   => route('listings.my'),
                'message'    => __('messages.listing_updated'),
                'listing_id' => $listing->id,
            ]);
        }

        return redirect()->route('listings.my')
            ->with('success', __('messages.listing_updated'));
    }

    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        // Prevent deletion if listing has pending payments
        if ($listing->payments()->where('status', 'pending')->exists()) {
            return redirect()->route('listings.my')
                ->with('error', __('messages.listing_has_pending_payments'));
        }

        // Delete all media files
        $disk = $this->listingDisk();
        foreach ($listing->media as $media) {
            Storage::disk($disk)->delete($media->path);
            if ($media->thumbnail_path) {
                Storage::disk($disk)->delete($media->thumbnail_path);
            }
        }

        $listing->delete();

        return redirect()->route('listings.my')
            ->with('success', __('messages.listing_deleted'));
    }

    public function myListings()
    {
        $listings = Auth::user()->listings()
            ->with('media')
            ->orderByRaw("CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 1 ELSE 0 END DESC", [now()])
            ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
            ->paginate(20);

        return view('listings.my', compact('listings'));
    }

    public function payment(Listing $listing)
    {
        try {
            $this->authorize('update', $listing);

            if (!in_array($listing->status, ['awaiting_payment', 'draft'])) {
                return redirect()->route('listings.my')
                    ->with('error', __('messages.listing_already_paid'));
            }

            $listing->load('media');
            $amount = $this->getPublishPrice($listing->category);
            $exchangeRate = Setting::getExchangeRate();

            return view('listings.payment', compact('listing', 'amount', 'exchangeRate'));
        } catch (\Throwable $e) {
            \Log::error('Payment page error: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }

    public function markAsSold(Listing $listing)
    {
        $this->authorize('update', $listing);

        $listing->update(['status' => 'sold']);

        return redirect()->route('listings.my')
            ->with('success', __('messages.listing_marked_sold'));
    }

    public function pause(Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status === 'active') {
            $listing->update(['status' => 'paused']);
        }

        return redirect()->route('listings.my')
            ->with('success', __('messages.listing_paused'));
    }

    public function reactivate(Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status === 'paused') {
            $listing->update(['status' => 'active']);
        }

        return redirect()->route('listings.my')
            ->with('success', __('messages.listing_reactivated'));
    }

    public function renew(Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return redirect()->route('listings.my')
                ->with('error', 'Seules les annonces actives peuvent être remontées.');
        }

        if (!$listing->canRenew()) {
            $days = $listing->daysUntilRenewal();
            return redirect()->route('listings.my')
                ->with('error', "Renouvellement disponible dans {$days} jour(s).");
        }

        $listing->update(['last_renewed_at' => now()]);

        return redirect()->route('listings.my')
            ->with('renewed', $listing->title);
    }

    public function feature(Listing $listing)
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return redirect()->route('listings.my')
                ->with('error', __('messages.listing_must_be_active'));
        }

        return view('listings.feature', [
            'listing' => $listing,
            'amount' => 12000,
        ]);
    }

    protected function listingDisk(): string
    {
        return config('filesystems.listing_disk', 'public');
    }

    protected function maxListingPriceDzd(): int
    {
        return 4294967295;
    }

    protected function filterListingPayloadForSchema(array $payload): array
    {
        try {
            $availableColumns = array_flip(\Schema::getColumnListing('listings'));
            $filteredPayload = array_intersect_key($payload, $availableColumns);
            $ignoredColumns = array_diff(array_keys($payload), array_keys($filteredPayload));

            if (!empty($ignoredColumns)) {
                \Log::warning('Listing payload skipped unknown columns', [
                    'ignored_columns' => array_values($ignoredColumns),
                ]);
            }

            return $filteredPayload;
        } catch (\Throwable $e) {
            \Log::warning('Failed to inspect listings schema before persisting payload', [
                'error' => $e->getMessage(),
            ]);

            return $payload;
        }
    }

    protected function handleImageUpload(Listing $listing, array $images): int
    {
        $storage = app(ListingMediaStorage::class);
        $order   = $listing->media()->max('order') ?? 0;
        $saved   = 0;

        foreach ($images as $image) {
            if ($storage->store($listing, $image, ++$order) !== null) {
                $saved++;
            }
        }

        return $saved;
    }

    protected function trackView(Listing $listing)
    {
        ListingView::recordView($listing, Auth::user(), request()->ip());
    }

    protected function getPublishPrice(string $category): int
    {
        return in_array($category, ['boat', 'jetski']) ? 5000 : 0;
    }

    protected function getWilayas(): array
    {
        return [
            'Algérie'             => '🇩🇿 Algérie',
            'Tunisie'             => '🇹🇳 Tunisie',
            'Maroc'               => '🇲🇦 Maroc',
            'Égypte'              => '🇪🇬 Égypte',
            'Espagne'             => '🇪🇸 Espagne',
            'France'              => '🇫🇷 France',
            'Italie'              => '🇮🇹 Italie',
            'Grèce'               => '🇬🇷 Grèce',
            'Croatie'             => '🇭🇷 Croatie',
            'Slovénie'            => '🇸🇮 Slovénie',
            'Turquie'             => '🇹🇷 Turquie',
            'Liban'               => '🇱🇧 Liban',
            'Malte'               => '🇲🇹 Malte',
            'Monaco'              => '🇲🇨 Monaco',
        ];
    }
}
