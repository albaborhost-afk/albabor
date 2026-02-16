<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class TodayOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.today-overview-widget';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    public function getTodayStats(): array
    {
        $today = Carbon::today();

        return [
            'newUsers' => User::whereDate('created_at', $today)->count(),
            'newListings' => Listing::whereDate('created_at', $today)->count(),
            'newPayments' => Payment::whereDate('created_at', $today)->count(),
            'approvedPayments' => Payment::whereDate('updated_at', $today)
                ->where('status', 'approved')
                ->count(),
            'todayRevenue' => Payment::whereDate('created_at', $today)
                ->where('status', 'approved')
                ->sum('amount_dzd'),
        ];
    }

    public function getComparisonStats(): array
    {
        $yesterday = Carbon::yesterday();

        return [
            'yesterdayUsers' => User::whereDate('created_at', $yesterday)->count(),
            'yesterdayListings' => Listing::whereDate('created_at', $yesterday)->count(),
            'yesterdayPayments' => Payment::whereDate('created_at', $yesterday)->count(),
        ];
    }
}
