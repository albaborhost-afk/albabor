<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsOverviewWidget;
use App\Filament\Widgets\BannerPerformanceWidget;
use App\Filament\Widgets\SiteTrafficChartWidget;
use App\Filament\Widgets\TopListingsByViewsWidget;
use App\Filament\Widgets\TopPayingUsersWidget;
use Filament\Pages\Page;

/**
 * Toutes les données de la plateforme au même endroit.
 *
 * Le tableau de bord d'accueil répond à « que dois-je traiter aujourd'hui ».
 * Cette page répond à « comment va la plateforme » : fréquentation, revenus
 * par client, audience des annonces et rendement des panneaux publicitaires.
 */
class Statistiques extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Statistiques';

    protected static ?string $title = 'Statistiques de la plateforme';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.statistiques';

    public function getSubheading(): ?string
    {
        return 'Fréquentation, paiements, audience des annonces et panneaux publicitaires.';
    }

    /**
     * Ordre voulu : d'abord les chiffres clés, puis le trafic, puis le détail
     * par client, par annonce et par bannière.
     *
     * Widgets d'en-tête (et non `getWidgets()`) : c'est le mécanisme des pages
     * personnalisées ; `getWidgets()` n'existe que sur le tableau de bord.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsOverviewWidget::class,
            SiteTrafficChartWidget::class,
            TopPayingUsersWidget::class,
            TopListingsByViewsWidget::class,
            BannerPerformanceWidget::class,
        ];
    }

    /** Une colonne : chaque bloc occupe toute la largeur et reste lisible. */
    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }
}
