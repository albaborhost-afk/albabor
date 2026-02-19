<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\ListingView;
use App\Models\Payment;
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

        // Wilaya filter
        if ($request->filled('wilaya')) {
            $query->byWilaya($request->wilaya);
        }

        // Etat filter
        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        // Type offre filter
        if ($request->filled('type_offre')) {
            $query->where('type_offre', $request->type_offre);
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

        // Sorting
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

        // Featured listings first
        $query->orderByDesc('featured_until');

        $listings = $query->paginate(20)->withQueryString();

        // Get wilayas for filter
        $wilayas = $this->getWilayas();

        return view('listings.index', compact('listings', 'wilayas'));
    }

    public function show(Listing $listing)
    {
        // Only show active listings to non-owners
        if ($listing->status !== 'active' && (!Auth::check() || Auth::id() !== $listing->user_id)) {
            abort(404);
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
            ->limit(4)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings'));
    }

    public function create()
    {
        $user = Auth::user();
        $wilayas = $this->getWilayas();

        $exchangeRate = Setting::getExchangeRate();

        return view('listings.create', compact('wilayas', 'exchangeRate'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate category permissions
        $category = $request->category;
        if (in_array($category, ['engine', 'parts'])) {
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
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'type_offre' => 'required|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
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

        // Create listing
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
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => $validated['numero_whatsapp'] ?? null,
            'numero_mobile' => $validated['numero_mobile'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
            'status' => 'awaiting_payment',
        ]);

        // Handle images
        $savedCount = $this->handleImageUpload($listing, $request->file('images'));

        if ($savedCount === 0) {
            $listing->delete();
            return back()->withErrors(['images' => 'Impossible de sauvegarder les images. Veuillez réessayer.'])->withInput();
        }

        // Create payment record
        $amount = $this->getPublishPrice($listing->category);

        return redirect()->route('listings.payment', $listing)
            ->with('success', __('messages.listing_created_payment_required'));
    }

    public function edit(Listing $listing)
    {
        try {
            $this->authorize('update', $listing);

            $listing->load('media');
            $wilayas = $this->getWilayas();
            $exchangeRate = Setting::getExchangeRate();

            return view('listings.edit', compact('listing', 'wilayas', 'exchangeRate'));
        } catch (\Throwable $e) {
            \Log::error('Edit page error: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Temporary: show error details to debug production issue
            return response('<h2>Debug Error</h2><pre>'
                . 'Message: ' . $e->getMessage() . "\n"
                . 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'Trace: ' . $e->getTraceAsString()
                . '</pre>', 500);
        }
    }

    public function update(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        $user = Auth::user();

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
            'price_dzd' => 'required|numeric|min:0',
            'currency' => 'required|in:DZD,EUR',
            'type_offre' => 'required|in:negociable,offert,fix',
            'etat' => 'required|in:jamais_utilise,comme_neuf,bon_etat,etat_moyen,a_reviser',
            'remarque_echange' => 'nullable|in:accepte,refuse',
            'wilaya' => 'nullable|string|max:100',
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
            'wilaya' => $validated['wilaya'] ?? null,
            'visible_a' => $validated['visible_a'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'numero_whatsapp' => $validated['numero_whatsapp'] ?? null,
            'numero_mobile' => $validated['numero_mobile'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'specs' => $validated['specs'] ?? null,
            'mediation_enabled' => $validated['mediation_enabled'] ?? false,
        ]);

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
            $maxNew = 10 - $currentCount;
            $newImages = array_slice($request->file('new_images'), 0, $maxNew);
            $this->handleImageUpload($listing, $newImages);
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
            ->orderBy('created_at', 'desc')
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

            return view('listings.payment', compact('listing', 'amount'));
        } catch (\Throwable $e) {
            \Log::error('Payment page error: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Temporary: show error details to debug production issue
            return response('<h2>Debug Error</h2><pre>'
                . 'Message: ' . $e->getMessage() . "\n"
                . 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'Trace: ' . $e->getTraceAsString()
                . '</pre>', 500);
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

    protected function handleImageUpload(Listing $listing, array $images): int
    {
        $disk = $this->listingDisk();
        $order = $listing->media()->max('order') ?? 0;
        $saved = 0;

        foreach ($images as $image) {
            $order++;

            // Generate unique filename
            $filename = uniqid('img_', true) . '.jpg';
            $path = 'listings/' . $listing->id . '/' . $filename;
            $thumbPath = 'listings/' . $listing->id . '/thumb_' . $filename;

            // Resize and save main image (max 1200px)
            $img = Image::read($image);
            $img->scaleDown(1200, 1200);
            $mainStored = Storage::disk($disk)->put($path, (string) $img->toJpeg(85));

            if (!$mainStored) {
                \Log::error('Failed to store listing image', ['path' => $path, 'disk' => $disk]);
                continue;
            }

            // Create thumbnail (300px)
            $thumb = Image::read($image);
            $thumb->cover(300, 300);
            $thumbStored = Storage::disk($disk)->put($thumbPath, (string) $thumb->toJpeg(75));

            ListingMedia::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'thumbnail_path' => $thumbStored ? $thumbPath : null,
                'order' => $order,
            ]);

            $saved++;
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
            '01' => 'Adrar',
            '02' => 'Chlef',
            '03' => 'Laghouat',
            '04' => 'Oum El Bouaghi',
            '05' => 'Batna',
            '06' => 'Béjaïa',
            '07' => 'Biskra',
            '08' => 'Béchar',
            '09' => 'Blida',
            '10' => 'Bouira',
            '11' => 'Tamanrasset',
            '12' => 'Tébessa',
            '13' => 'Tlemcen',
            '14' => 'Tiaret',
            '15' => 'Tizi Ouzou',
            '16' => 'Alger',
            '17' => 'Djelfa',
            '18' => 'Jijel',
            '19' => 'Sétif',
            '20' => 'Saïda',
            '21' => 'Skikda',
            '22' => 'Sidi Bel Abbès',
            '23' => 'Annaba',
            '24' => 'Guelma',
            '25' => 'Constantine',
            '26' => 'Médéa',
            '27' => 'Mostaganem',
            '28' => 'M\'Sila',
            '29' => 'Mascara',
            '30' => 'Ouargla',
            '31' => 'Oran',
            '32' => 'El Bayadh',
            '33' => 'Illizi',
            '34' => 'Bordj Bou Arréridj',
            '35' => 'Boumerdès',
            '36' => 'El Tarf',
            '37' => 'Tindouf',
            '38' => 'Tissemsilt',
            '39' => 'El Oued',
            '40' => 'Khenchela',
            '41' => 'Souk Ahras',
            '42' => 'Tipaza',
            '43' => 'Mila',
            '44' => 'Aïn Defla',
            '45' => 'Naâma',
            '46' => 'Aïn Témouchent',
            '47' => 'Ghardaïa',
            '48' => 'Relizane',
            '49' => 'El M\'Ghair',
            '50' => 'El Meniaa',
            '51' => 'Ouled Djellal',
            '52' => 'Bordj Baji Mokhtar',
            '53' => 'Béni Abbès',
            '54' => 'Timimoun',
            '55' => 'Touggourt',
            '56' => 'Djanet',
            '57' => 'In Salah',
            '58' => 'In Guezzam',
            '59' => 'Bir El Ater',
            '60' => 'Oued Zenati',
            '61' => 'Tazmalt',
            '62' => 'Ain Oussera',
            '63' => 'Hassi Messaoud',
            '64' => 'Meghaier',
            '65' => 'Ain Beida',
            '66' => 'Babar',
            '67' => 'El Abiodh Sidi Cheikh',
            '68' => 'Sidi Amrane',
            '69' => 'Oued Rhiou',
        ];
    }
}
