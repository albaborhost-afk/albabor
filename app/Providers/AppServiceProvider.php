<?php

namespace App\Providers;

use App\Models\Listing;
use App\Models\Payment;
use App\Policies\ListingPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Listing::class, ListingPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
    }
}
