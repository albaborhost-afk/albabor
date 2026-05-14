<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panneau "Espace Vendeur" — back-office dédié aux vendeurs de pièces / moteurs.
 * Accessible sur /vendeur, distinct du panneau admin (/admin).
 */
class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendeur')
            ->path('vendeur')
            ->login()
            ->brandName('Albabor Pro')
            ->brandLogo(asset('vendor-panel/logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('vendor-panel/favicon.svg'))
            ->colors([
                // Identité visuelle vendeur : Teal + Gold (distincte du Navy admin)
                'primary' => Color::hex('#17A2B8'),
                'info' => Color::hex('#2471A3'),
                'warning' => Color::hex('#F39C12'),
                'success' => Color::hex('#28A745'),
                'danger' => Color::hex('#FF6B6B'),
            ])
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Ma boutique')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Annonces')
                    ->icon('heroicon-o-rectangle-stack')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Abonnement')
                    ->icon('heroicon-o-credit-card')
                    ->collapsed(false),
            ])
            ->darkMode(true)
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Vendor/Resources'), for: 'App\\Filament\\Vendor\\Resources')
            ->discoverPages(in: app_path('Filament/Vendor/Pages'), for: 'App\\Filament\\Vendor\\Pages')
            ->pages([
                \App\Filament\Vendor\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Vendor/Widgets'), for: 'App\\Filament\\Vendor\\Widgets')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.vendor.branding.styles')->render(),
            )
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn (): string => view('filament.vendor.branding.banner')->render(),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => view('filament.vendor.branding.footer')->render(),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
