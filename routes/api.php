<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MediationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public (no auth) ──────────────────────────────────────

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    // Public listings
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/featured', [ListingController::class, 'featured']);
    Route::get('/listings/{listing}', [ListingController::class, 'show']);

    // Public vendor profile
    Route::get('/vendors/{user}', [ListingController::class, 'vendorProfile']);

    // Settings
    Route::get('/settings/exchange-rate', [SettingsController::class, 'exchangeRate']);

    // Subscription plans (public)
    Route::get('/plans', [SubscriptionController::class, 'plans']);

    // ── Authenticated ─────────────────────────────────────────

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::post('/profile/picture', [ProfileController::class, 'updatePicture']);
        Route::delete('/profile/picture', [ProfileController::class, 'deletePicture']);
        Route::post('/profile/verification', [ProfileController::class, 'submitVerification']);
        Route::post('/profile/upgrade-vendor', [ProfileController::class, 'upgradeToVendor']);

        // My Listings
        Route::get('/my-listings', [ListingController::class, 'myListings']);
        Route::post('/listings', [ListingController::class, 'store']);
        Route::put('/listings/{listing}', [ListingController::class, 'update']);
        Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);
        Route::post('/listings/{listing}/sold', [ListingController::class, 'markAsSold']);
        Route::post('/listings/{listing}/pause', [ListingController::class, 'pause']);
        Route::post('/listings/{listing}/reactivate', [ListingController::class, 'reactivate']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{listing}', [FavoriteController::class, 'toggle']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments/listing/{listing}', [PaymentController::class, 'storeListingPayment']);
        Route::post('/payments/feature/{listing}', [PaymentController::class, 'storeFeaturePayment']);
        Route::post('/payments/subscription', [PaymentController::class, 'storeSubscriptionPayment']);
        Route::post('/payments/mediation', [PaymentController::class, 'storeMediationPayment']);

        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/active', [SubscriptionController::class, 'active']);

        // Mediation
        Route::get('/mediation', [MediationController::class, 'index']);
        Route::post('/mediation/{listing}', [MediationController::class, 'store']);
        Route::get('/mediation/{ticket}', [MediationController::class, 'show']);
        Route::post('/mediation/{ticket}/message', [MediationController::class, 'addMessage']);
        Route::post('/mediation/{ticket}/cancel', [MediationController::class, 'cancel']);
    });
});
