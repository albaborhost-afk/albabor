<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MediationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public (no auth) ──────────────────────────────────────

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/auth/google', [App\Http\Controllers\Auth\GoogleMobileAuthController::class, 'login']);

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

    // Banners (public)
    Route::get('/banners', [BannerController::class, 'index']);
    Route::post('/banners/{banner}/click', [BannerController::class, 'trackClick']);

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
        Route::post('/listings', [ListingController::class, 'store'])->middleware('throttle:10,1');
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
        Route::post('/payments/listing/{listing}', [PaymentController::class, 'storeListingPayment'])->middleware('throttle:5,1');
        Route::post('/payments/feature/{listing}', [PaymentController::class, 'storeFeaturePayment'])->middleware('throttle:5,1');
        Route::post('/payments/subscription', [PaymentController::class, 'storeSubscriptionPayment'])->middleware('throttle:5,1');
        Route::post('/payments/mediation', [PaymentController::class, 'storeMediationPayment']);

        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/active', [SubscriptionController::class, 'active']);

        // Conversations
        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::get('/conversations/unread-count', [ConversationController::class, 'unreadCount']);
        Route::post('/conversations/listing/{listing}', [ConversationController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->middleware('throttle:30,1');

        // Mediation
        Route::get('/mediation', [MediationController::class, 'index']);
        Route::post('/mediation/{listing}', [MediationController::class, 'store'])->middleware('throttle:5,1');
        Route::get('/mediation/{ticket}', [MediationController::class, 'show']);
        Route::post('/mediation/{ticket}/message', [MediationController::class, 'addMessage']);
        Route::post('/mediation/{ticket}/cancel', [MediationController::class, 'cancel']);
    });
});
