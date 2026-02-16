<?php

namespace App\Providers\Filament;

use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Albabor Admin')
            ->colors([
                // Primary: Navy blue matching iOS app
                'primary' => Color::hex('#1B4F72'),
                // Secondary/Info: Teal accent matching iOS app
                'info' => Color::hex('#17A2B8'),
                // Danger: Coral matching iOS app
                'danger' => Color::hex('#FF6B6B'),
                // Success: Keep a complementary green
                'success' => Color::hex('#28A745'),
                // Warning: Amber for warnings
                'warning' => Color::hex('#FFC107'),
            ])
            ->authGuard('web')
            ->authPasswordBroker('users')
            // Navigation Groups in French
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Gestion')
                    ->icon('heroicon-o-briefcase')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Utilisateurs')
                    ->icon('heroicon-o-users')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Abonnements')
                    ->icon('heroicon-o-credit-card')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('Support')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsed(false),
            ])
            // Dark mode toggle
            ->darkMode(true)
            // SPA mode for faster navigation
            ->spa()
            // Sidebar collapsible
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\PendingActionsWidget::class,
                \App\Filament\Widgets\RecentListingsWidget::class,
                \App\Filament\Widgets\RecentPaymentsWidget::class,
            ])
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
