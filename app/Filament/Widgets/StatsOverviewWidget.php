<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\User;
use App\Models\VerificationRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeListingsCount = Listing::where('status', 'active')->count();
        $pendingPaymentsCount = Payment::where('status', 'pending')->count();
        $totalUsersCount = User::count();
        $pendingVerificationsCount = VerificationRequest::where('status', 'pending')->count();

        // Calculate change from last week
        $lastWeekActiveListings = Listing::where('status', 'active')
            ->where('created_at', '<', now()->subWeek())
            ->count();
        $activeListingsChange = $lastWeekActiveListings > 0
            ? round((($activeListingsCount - $lastWeekActiveListings) / $lastWeekActiveListings) * 100, 1)
            : 0;

        $lastWeekUsers = User::where('created_at', '<', now()->subWeek())->count();
        $usersChange = $lastWeekUsers > 0
            ? round((($totalUsersCount - $lastWeekUsers) / $lastWeekUsers) * 100, 1)
            : 0;

        return [
            Stat::make('Annonces Actives', number_format($activeListingsCount))
                ->description($activeListingsChange >= 0 ? "+{$activeListingsChange}% cette semaine" : "{$activeListingsChange}% cette semaine")
                ->descriptionIcon($activeListingsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, $activeListingsCount > 0 ? 5 : 0])
                ->icon('heroicon-o-document-text'),

            Stat::make('Paiements en attente', number_format($pendingPaymentsCount))
                ->description($pendingPaymentsCount > 0 ? 'Requiert votre attention' : 'Tout est traite')
                ->descriptionIcon($pendingPaymentsCount > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color('warning')
                ->chart([2, 4, 3, 5, 4, 3, $pendingPaymentsCount])
                ->icon('heroicon-o-credit-card'),

            Stat::make('Utilisateurs', number_format($totalUsersCount))
                ->description($usersChange >= 0 ? "+{$usersChange}% cette semaine" : "{$usersChange}% cette semaine")
                ->descriptionIcon($usersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart([4, 5, 3, 7, 4, 5, $totalUsersCount > 0 ? 6 : 0])
                ->icon('heroicon-o-users'),

            Stat::make('Demandes de verification', number_format($pendingVerificationsCount))
                ->description($pendingVerificationsCount > 0 ? "{$pendingVerificationsCount} en attente" : 'Aucune demande')
                ->descriptionIcon($pendingVerificationsCount > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color('info')
                ->chart([1, 2, 3, 2, 1, 2, $pendingVerificationsCount])
                ->icon('heroicon-o-shield-check'),
        ];
    }
}
