<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Listing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statistiques de l'Espace Vendeur.
 * Toutes les donnees sont scopees au vendeur connecte
 * et limitees aux categories 'engine' et 'parts'.
 */
class VendorStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $vendorId = auth()->id();

        $baseQuery = fn () => Listing::query()
            ->where('user_id', $vendorId)
            ->whereIn('category', ['engine', 'parts']);

        $activeListingsCount = $baseQuery()->where('status', 'active')->count();
        $soldListingsCount = $baseQuery()->where('status', 'sold')->count();
        $totalViews = (int) $baseQuery()->sum('views_count');
        $totalFavorites = (int) $baseQuery()->sum('favorites_count');

        return [
            Stat::make('Annonces actives', number_format($activeListingsCount, 0, ',', ' '))
                ->description($activeListingsCount > 0 ? 'En ligne actuellement' : 'Aucune annonce active')
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon('heroicon-o-document-text')
                ->color('teal'),

            Stat::make('Total des vues', number_format($totalViews, 0, ',', ' '))
                ->description('Vues cumulees sur vos annonces')
                ->descriptionIcon('heroicon-m-eye')
                ->icon('heroicon-o-eye')
                ->color('warning'),

            Stat::make('Total des favoris', number_format($totalFavorites, 0, ',', ' '))
                ->description('Mises en favori par les acheteurs')
                ->descriptionIcon('heroicon-m-heart')
                ->icon('heroicon-o-heart')
                ->color('warning'),

            Stat::make('Annonces vendues', number_format($soldListingsCount, 0, ',', ' '))
                ->description($soldListingsCount > 0 ? 'Ventes realisees' : 'Aucune vente pour le moment')
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-o-shopping-bag')
                ->color('success'),
        ];
    }
}
