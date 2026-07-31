<?php

namespace App\Filament\Widgets;

use App\Models\Banner;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\SiteVisit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Chiffres clés de la page Statistiques.
 *
 * Chaque carte compare à la période précédente : un nombre seul ne dit pas
 * si la situation s'améliore.
 */
class AnalyticsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $visitorsToday     = SiteVisit::uniqueVisitorsOn($today);
        $visitorsYesterday = SiteVisit::uniqueVisitorsOn($yesterday);
        $pageViewsToday    = SiteVisit::pageViewsOn($today);

        // 7 derniers jours contre les 7 précédents.
        $visitors7  = SiteVisit::whereBetween('visit_date', [now()->subDays(6)->toDateString(), $today])->count();
        $visitors14 = SiteVisit::whereBetween('visit_date', [now()->subDays(13)->toDateString(), now()->subDays(7)->toDateString()])->count();

        $revenueMonth = (int) Payment::where('status', 'approved')
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('amount_dzd');
        $revenuePrevMonth = (int) Payment::where('status', 'approved')
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum('amount_dzd');

        $listingViews   = (int) Listing::sum('views_count');
        $bannerViews    = (int) Banner::sum('view_count');
        $bannerClicks   = (int) Banner::sum('click_count');
        $bannerCtr      = $bannerViews > 0 ? round(($bannerClicks / $bannerViews) * 100, 2) : 0.0;

        $payingUsers = Payment::where('status', 'approved')->distinct('user_id')->count('user_id');

        return [
            Stat::make('Visiteurs aujourd\'hui', number_format($visitorsToday, 0, ',', ' '))
                ->description($this->trend($visitorsToday, $visitorsYesterday, 'qu\'hier'))
                ->descriptionIcon($visitorsToday >= $visitorsYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($visitorsToday >= $visitorsYesterday ? 'success' : 'danger')
                ->chart($this->visitorSparkline()),

            Stat::make('Pages vues aujourd\'hui', number_format($pageViewsToday, 0, ',', ' '))
                ->description($visitorsToday > 0
                    ? round($pageViewsToday / max(1, $visitorsToday), 1) . ' pages par visiteur'
                    : 'Aucune visite pour l\'instant')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('info'),

            Stat::make('Visiteurs (7 jours)', number_format($visitors7, 0, ',', ' '))
                ->description($this->trend($visitors7, $visitors14, 'que les 7 jours précédents'))
                ->descriptionIcon($visitors7 >= $visitors14 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($visitors7 >= $visitors14 ? 'success' : 'warning'),

            Stat::make('Revenus ce mois', number_format($revenueMonth, 0, ',', ' ') . ' DA')
                ->description($this->trend($revenueMonth, $revenuePrevMonth, 'que le mois dernier'))
                ->descriptionIcon($revenueMonth >= $revenuePrevMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueMonth >= $revenuePrevMonth ? 'success' : 'danger'),

            Stat::make('Clients ayant payé', number_format($payingUsers, 0, ',', ' '))
                ->description('Comptes avec au moins un paiement validé')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Vues d\'annonces (total)', number_format($listingViews, 0, ',', ' '))
                ->description('Cumul sur toutes les annonces')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make('Diffusions de bannières', number_format($bannerViews, 0, ',', ' '))
                ->description(number_format($bannerClicks, 0, ',', ' ') . ' clics')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('warning'),

            Stat::make('Taux de clic bannières', $bannerCtr . ' %')
                ->description($bannerCtr >= 1 ? 'Performance correcte' : 'Sous la moyenne du secteur (~1 %)')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color($bannerCtr >= 1 ? 'success' : 'gray'),
        ];
    }

    /** « +25 % de plus qu'hier », ou un texte clair quand il n'y a rien à comparer. */
    private function trend(int $current, int $previous, string $versus): string
    {
        if ($previous === 0) {
            return $current === 0
                ? 'Aucune donnée à comparer'
                : 'Première période mesurée';
        }

        $delta = round((($current - $previous) / $previous) * 100);

        if ($delta === 0.0) {
            return 'Stable ' . $versus;
        }

        return ($delta > 0 ? '+' : '') . $delta . ' % ' . $versus;
    }

    /** Visiteurs uniques des 7 derniers jours, pour la mini-courbe. */
    private function visitorSparkline(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => SiteVisit::uniqueVisitorsOn(now()->subDays($daysAgo)->toDateString()))
            ->all();
    }
}
