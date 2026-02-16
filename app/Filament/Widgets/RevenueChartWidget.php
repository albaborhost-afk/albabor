<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenus des 7 derniers jours';

    protected static ?string $description = 'Evolution des paiements approuves';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 2;

    protected static ?string $maxHeight = '280px';

    protected static string $color = 'info';

    protected function getData(): array
    {
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            $revenue = Payment::where('status', 'approved')
                ->whereDate('created_at', $date)
                ->sum('amount_dzd');

            return [
                'date' => $date->locale('fr')->isoFormat('ddd'),
                'revenue' => $revenue,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenus (DZD)',
                    'data' => $data->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(23, 162, 184, 0.2)',
                    'borderColor' => 'rgb(23, 162, 184)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgb(23, 162, 184)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return value.toLocaleString('fr-FR') + ' DA'; }",
                    ],
                ],
            ],
        ];
    }
}
