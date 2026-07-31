<?php

namespace App\Filament\Widgets;

use App\Models\SiteVisit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Fréquentation du site jour par jour.
 *
 * Deux courbes : visiteurs uniques (combien de personnes) et pages vues
 * (à quel point elles explorent). L'écart entre les deux dit si les visiteurs
 * restent ou repartent tout de suite.
 */
class SiteTrafficChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Fréquentation du site';

    protected static ?string $description = 'Visiteurs uniques et pages vues, par jour';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7'  => '7 derniers jours',
            '30' => '30 derniers jours',
            '90' => '3 derniers mois',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 30);

        // Une seule requête pour toute la période : interroger jour par jour
        // ferait 90 requêtes sur le filtre le plus large.
        $rows = SiteVisit::query()
            ->selectRaw('visit_date, COUNT(*) as visitors, SUM(page_views) as page_views')
            ->where('visit_date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('visit_date')
            ->pluck('page_views', 'visit_date')
            ->all();

        $visitorRows = SiteVisit::query()
            ->selectRaw('visit_date, COUNT(*) as visitors')
            ->where('visit_date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('visit_date')
            ->pluck('visitors', 'visit_date')
            ->all();

        $labels    = [];
        $visitors  = [];
        $pageViews = [];

        foreach (range($days - 1, 0) as $daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            $key  = $date->toDateString();

            // Les jours sans visite doivent apparaître à zéro, pas disparaître.
            $labels[]    = $date->locale('fr')->isoFormat($days > 31 ? 'D MMM' : 'ddd D');
            $visitors[]  = (int) ($visitorRows[$key] ?? 0);
            $pageViews[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'                => 'Visiteurs uniques',
                    'data'                 => $visitors,
                    'backgroundColor'      => 'rgba(27, 79, 114, 0.20)',
                    'borderColor'          => 'rgb(27, 79, 114)',
                    'borderWidth'          => 3,
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointRadius'          => $days > 31 ? 0 : 3,
                    'pointHoverRadius'     => 6,
                    'pointBackgroundColor' => 'rgb(27, 79, 114)',
                ],
                [
                    'label'                => 'Pages vues',
                    'data'                 => $pageViews,
                    'backgroundColor'      => 'rgba(23, 162, 184, 0.12)',
                    'borderColor'          => 'rgb(23, 162, 184)',
                    'borderWidth'          => 2,
                    'borderDash'           => [6, 4],
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointRadius'          => $days > 31 ? 0 : 3,
                    'pointHoverRadius'     => 6,
                    'pointBackgroundColor' => 'rgb(23, 162, 184)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                    'labels'   => ['usePointStyle' => true, 'boxWidth' => 8],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['precision' => 0],
                ],
            ],
            'interaction' => [
                'mode'      => 'index',
                'intersect' => false,
            ],
        ];
    }
}
