<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Payment;
use App\Policies\ConversationPolicy;
use App\Policies\ListingPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Artisan;
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
        Gate::policy(Conversation::class, ConversationPolicy::class);

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $siteUrl = rtrim(config('app.url'), '/');

            return (new MailMessage)
                ->subject(__('passwords.email.subject'))
                ->action(__('passwords.email.button'), $resetUrl)
                ->view([
                    'html' => 'emails.auth.reset-password',
                    'text' => 'emails.auth.reset-password-text',
                ], [
                    'locale' => app()->getLocale(),
                    'siteUrl' => $siteUrl,
                    'logoUrl' => $siteUrl.'/images/logo-full.png',
                    'expiryMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                ]);
        });

        // Auto-create storage symlink if missing (for production/Laravel Cloud)
        if (!file_exists(public_path('storage'))) {
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                // Silently fail - some environments don't support symlinks
            }
        }
    }
}
