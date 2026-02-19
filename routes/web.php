<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingMediaController;
use App\Http\Controllers\MediationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Models\Listing;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    $featuredListings = Listing::where('status', 'active')
        ->where('featured_until', '>', now())
        ->latest()
        ->take(4)
        ->get();

    $latestListings = Listing::where('status', 'active')
        ->latest()
        ->take(8)
        ->get();

    return view('welcome', compact('featuredListings', 'latestListings'));
})->name('home');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Google OAuth
    Route::get('auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});

// Public listing routes
Route::get('annonces', [ListingController::class, 'index'])->name('listings.index');
Route::get('media/listings/{media}/{variant?}', [ListingMediaController::class, 'show'])->name('listing-media.show');

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
    Route::post('annonces', [ListingController::class, 'store'])->name('listings.store');
    Route::get('annonces/{listing}/modifier', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('annonces/{listing}', [ListingController::class, 'update'])->name('listings.update');
    Route::delete('annonces/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::get('annonces/{listing}/paiement', [ListingController::class, 'payment'])->name('listings.payment');
    Route::post('annonces/{listing}/vendu', [ListingController::class, 'markAsSold'])->name('listings.sold');
    Route::post('annonces/{listing}/pause', [ListingController::class, 'pause'])->name('listings.pause');
    Route::post('annonces/{listing}/reactiver', [ListingController::class, 'reactivate'])->name('listings.reactivate');
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

    // Mediation
    Route::get('mediation', [MediationController::class, 'index'])->name('mediation.index');
    Route::get('mediation/creer/{listing}', [MediationController::class, 'create'])->name('mediation.create');
    Route::post('mediation/{listing}', [MediationController::class, 'store'])->name('mediation.store');
    Route::get('mediation/{ticket}', [MediationController::class, 'show'])->name('mediation.show');
    Route::post('mediation/{ticket}/message', [MediationController::class, 'addMessage'])->name('mediation.message');
    Route::post('mediation/{ticket}/annuler', [MediationController::class, 'cancel'])->name('mediation.cancel');
});

// Public listing detail (must be after /annonces/creer to avoid wildcard conflict)
Route::get('annonces/{listing}', [ListingController::class, 'show'])->name('listings.show');
