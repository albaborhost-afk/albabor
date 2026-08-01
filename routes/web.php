<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingMediaController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MediationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorOnboardingController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\BannerRequestController;
use App\Http\Controllers\SellerProfileController;
use App\Models\Listing;
use Illuminate\Support\Facades\Route;

// Language switcher
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['fr', 'en', 'es'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back()->withInput();
})->name('lang.switch');

// Home
Route::get('/', function () {
    $featuredListings = Listing::where('status', 'active')
        ->where('featured_until', '>', now())
        ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
        ->take(4)
        ->get();

    $latestListings = Listing::where('status', 'active')
        ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')
        ->take(25)
        ->get();

    $banners = \App\Models\Banner::active()->take(10)->get();

    // Le site ne comptait aucune diffusion : les chiffres montrés à
    // l'annonceur ne reflétaient que l'application mobile.
    \App\Models\Banner::recordImpressions($banners);

    return view('welcome', compact('featuredListings', 'latestListings', 'banners'));
})->name('home');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:3,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:3,1')->name('password.email');

    // Google OAuth
    Route::get('auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});

// Public static pages (legal)
Route::view('politique-confidentialite', 'pages.privacy')->name('pages.privacy');
Route::view('conditions-utilisation', 'pages.terms')->name('pages.terms');
Route::view('mentions-legales', 'pages.legal-notice')->name('pages.legal');
Route::view('autorisation-vente', 'pages.sale-authorization')->name('pages.sale-authorization');

// Public listing routes
Route::get('annonces', [ListingController::class, 'index'])->name('listings.index');
Route::get('media/listings/{media}/{variant?}', [ListingMediaController::class, 'show'])->name('listing-media.show');

// Profile picture proxy (serves from S3 privately)
Route::get('media/profile/{userId}', function (int $userId) {
    $user = \App\Models\User::findOrFail($userId);

    // Compte publiant sous « Invité » : la photo est masquée dans les vues,
    // elle ne doit pas rester accessible en devinant l'identifiant.
    if ($user->identityMasked()) {
        abort(404);
    }

    // Base64 stored in DB (legacy or fallback)
    if ($user->profile_picture_data && str_starts_with($user->profile_picture_data, 'data:')) {
        $parts = explode(',', $user->profile_picture_data, 2);
        $content = base64_decode($parts[1] ?? '');
        return response($content, 200, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    if (!$user->profile_picture) {
        abort(404);
    }

    $diskName = config('filesystems.listing_disk', 'public');
    $disk     = \Storage::disk($diskName);

    // Comme pour les photos d'annonces : sur S3 on redirige vers un lien
    // signé au lieu de faire transiter le fichier par un processus PHP.
    if (config("filesystems.disks.{$diskName}.driver") === 's3') {
        try {
            return redirect()->away(
                $disk->temporaryUrl($user->profile_picture, now()->addHours(6)),
                302,
                ['Cache-Control' => 'private, max-age=3600'],
            );
        } catch (\Throwable $e) {
            \Log::warning('Signed profile picture URL unavailable', ['user_id' => $user->id]);
        }
    }

    try {
        $content = $disk->get($user->profile_picture);
    } catch (\Throwable) {
        abort(404);
    }

    if ($content === null) {
        abort(404);
    }

    return response($content, 200, [
        'Content-Type'  => 'image/jpeg',
        'Cache-Control' => 'public, max-age=2592000',
    ]);
})->name('profile.picture');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Profile
    Route::get('profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('profil/verification', [ProfileController::class, 'verificationForm'])->name('profile.verification');
    Route::post('profil/verification', [ProfileController::class, 'submitVerification'])->name('profile.verification.submit');
    Route::get('profil/devenir-vendeur', [ProfileController::class, 'upgradeToVendor'])->name('profile.upgrade-vendor');
    Route::post('profil/devenir-vendeur', [ProfileController::class, 'confirmUpgradeToVendor'])->name('profile.upgrade-vendor.confirm');

    // My Listings
    Route::get('mes-annonces', [ListingController::class, 'myListings'])->name('listings.my');
    Route::get('annonces/creer', [ListingController::class, 'create'])->name('listings.create');
    Route::post('annonces', [ListingController::class, 'store'])->middleware('throttle:10,1')->name('listings.store');
    // Reprise d'un envoi dont la réponse s'est perdue (timeout, coupure réseau).
    Route::get('annonces/etat-envoi', [ListingController::class, 'submissionStatus'])
        ->middleware('throttle:60,1')
        ->name('listings.submission-status');
    Route::get('annonces/{listing}/modifier', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('annonces/{listing}', [ListingController::class, 'update'])->name('listings.update');
    Route::delete('annonces/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::get('annonces/{listing}/paiement', [ListingController::class, 'payment'])->name('listings.payment');
    Route::post('annonces/{listing}/vendu', [ListingController::class, 'markAsSold'])->name('listings.sold');
    Route::post('annonces/{listing}/pause', [ListingController::class, 'pause'])->name('listings.pause');
    Route::post('annonces/{listing}/reactiver', [ListingController::class, 'reactivate'])->name('listings.reactivate');
    Route::post('annonces/{listing}/renouveler', [ListingController::class, 'renew'])->name('listings.renew');
    Route::get('annonces/{listing}/mettre-en-avant', [ListingController::class, 'feature'])->name('listings.feature');

    // Favorites
    Route::get('favoris', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('favoris/{listing}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Payments
    Route::get('paiements', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('paiements/annonce/{listing}', [PaymentController::class, 'storeListingPayment'])->name('payments.listing');
    Route::post('paiements/mise-en-avant/{listing}', [PaymentController::class, 'storeFeaturePayment'])->name('payments.feature');
    Route::get('abonnements', [PaymentController::class, 'subscriptionPlans'])->name('subscription.plans');
    Route::post('paiements/abonnement', [PaymentController::class, 'storeSubscriptionPayment'])->name('payments.subscription');
    Route::post('paiements/mediation', [PaymentController::class, 'storeMediationPayment'])->name('payments.mediation');

    // Stripe
    Route::post('paiements/stripe/annonce/{listing}', [PaymentController::class, 'stripeCheckout'])->name('payments.stripe.checkout');
    Route::get('paiements/stripe/success', [PaymentController::class, 'stripeSuccess'])->name('payments.stripe.success');

    // Messages
    Route::get('messages', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('messages/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::get('messages/{conversation}/json', [ConversationController::class, 'messages'])->name('conversations.messages');
    Route::post('messages/annonce/{listing}', [ConversationController::class, 'store'])->middleware('throttle:10,1')->name('conversations.store');
    Route::post('messages/{conversation}/repondre', [ConversationController::class, 'reply'])->middleware('throttle:30,1')->name('conversations.reply');

    // Mediation
    Route::get('mediation', [MediationController::class, 'index'])->name('mediation.index');
    Route::get('mediation/creer/{listing}', [MediationController::class, 'create'])->name('mediation.create');
    Route::post('mediation/{listing}', [MediationController::class, 'store'])->middleware('throttle:5,1')->name('mediation.store');
    Route::get('mediation/{ticket}', [MediationController::class, 'show'])->name('mediation.show');
    Route::post('mediation/{ticket}/message', [MediationController::class, 'addMessage'])->name('mediation.message');
    Route::post('mediation/{ticket}/annuler', [MediationController::class, 'cancel'])->name('mediation.cancel');
});

// ── Espace Vendeur (boutiques de pièces / moteurs) ─────────────
// Landing publique + vitrines boutiques
Route::get('espace-vendeur', [VendorOnboardingController::class, 'landing'])->name('vendor.landing');
Route::get('boutiques', [BoutiqueController::class, 'index'])->name('boutiques.index');
Route::get('boutique/{vendorProfile}', [BoutiqueController::class, 'show'])->name('boutiques.show');

// Profil public d'un vendeur (particulier ou pro) : toutes ses annonces.
// Pluriel volontaire : /vendeur est déjà le panel Filament des boutiques.
Route::get('vendeurs/{user}', [SellerProfileController::class, 'show'])->name('sellers.show');

// Demande d'espace publicitaire — ouvert aux visiteurs, sans compte.
Route::get('publicite', [BannerRequestController::class, 'create'])->name('publicite.create');
Route::post('publicite', [BannerRequestController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('publicite.store');

// Onboarding vendeur (authentification requise)
Route::middleware('auth')->group(function () {
    Route::get('espace-vendeur/demarrer', [VendorOnboardingController::class, 'create'])->name('vendor.onboarding.create');
    Route::post('espace-vendeur/demarrer', [VendorOnboardingController::class, 'store'])
        ->middleware('throttle:5,1')->name('vendor.onboarding.store');
});

// Stripe Webhook (exempt from CSRF)
Route::post('stripe/webhook', [PaymentController::class, 'stripeWebhook'])->name('payments.stripe.webhook');

// Banner click tracking
Route::get('/banner/{banner}/click', [\App\Http\Controllers\BannerController::class, 'trackClick'])->name('banners.click');

// Public listing detail (must be after /annonces/creer to avoid wildcard conflict)
Route::get('annonces/{listing}', [ListingController::class, 'show'])->name('listings.show');
