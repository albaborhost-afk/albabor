<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Listing;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Evolution du nombre d'annonces publiees par le vendeur connecte
 * sur les 6 derniers mois. Limite aux categories 'engine' et 'parts'.
 */
class VendorListingsChart extends ChartWidget
{
    protected static ?string $heading = 'Annonces publiées (6 derniers mois)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected static string $color = 'info';

    protected function getData(): array
    {
        $vendorId = auth()->id();

        $data = collect(range(5, 0))->map(function ($monthsAgo) use ($vendorId) {
            $month = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

            $count = Listing::query()
                ->where('user_id', $vendorId)
                ->whereIn('category', ['engine', 'parts'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            return [
                'label' => $month->locale('fr')->isoFormat('MMM YYYY'),
                'count' => $count,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Annonces publiées',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(23, 162, 184, 0.5)',
                    'borderColor' => 'rgb(23, 162, 184)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
