<?php

namespace App\Filament\Vendor\Widgets;

use Filament\Widgets\Widget;

/**
 * Banniere d'accueil de l'Espace Vendeur.
 * Affiche un message de bienvenue personnalise avec le nom de la boutique.
 */
class VendorWelcomeWidget extends Widget
{
    protected static string $view = 'filament.vendor.widgets.vendor-welcome-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Bonjour';
        }

        if ($hour < 18) {
            return 'Bon après-midi';
        }

        return 'Bonsoir';
    }

    public function getShopName(): string
    {
        $user = auth()->user();

        return $user?->vendorProfile?->shop_name
            ?? $user?->name
            ?? 'Vendeur';
    }

    public function getTodayDate(): string
    {
        return now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
    }

    public function getBoutiqueUrl(): ?string
    {
        return route('filament.vendeur.pages.manage-boutique', absolute: false);
    }
}
