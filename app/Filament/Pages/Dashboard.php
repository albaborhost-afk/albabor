<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityFeedWidget;
use App\Filament\Widgets\PendingActionsWidget;
use App\Filament\Widgets\QuickStatsWidget;
use App\Filament\Widgets\RecentListingsWidget;
use App\Filament\Widgets\RecentPaymentsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\TodayOverviewWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            QuickStatsWidget::class,
            PendingActionsWidget::class,
            TodayOverviewWidget::class,
            RevenueChartWidget::class,
            ActivityFeedWidget::class,
            RecentListingsWidget::class,
            RecentPaymentsWidget::class,
        ];
    }
}
