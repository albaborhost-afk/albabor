<?php

namespace App\Filament\Vendor\Pages;

use App\Filament\Vendor\Widgets\VendorListingsChart;
use App\Filament\Vendor\Widgets\VendorStatsOverview;
use App\Filament\Vendor\Widgets\VendorWelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Tableau de bord de l'Espace Vendeur.
 * Les widgets statistiques sont scopés au vendeur connecté
 * (catégories 'engine' et 'parts' uniquement).
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de bord';

    protected static ?string $navigationLabel = 'Tableau de bord';

    /**
     * Ordre explicite des widgets : bienvenue, statistiques, graphique.
     */
    public function getWidgets(): array
    {
        return [
            VendorWelcomeWidget::class,
            VendorStatsOverview::class,
            VendorListingsChart::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
