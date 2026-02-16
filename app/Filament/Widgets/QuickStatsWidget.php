<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\Widget;

class QuickStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-stats-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $activeListings = Listing::where('status', 'active')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalRevenue = Payment::where('status', 'approved')->sum('amount_dzd');
        $featuredListings = Listing::whereNotNull('featured_until')
            ->where('featured_until', '>', now())
            ->count();
        $vendorCount = User::where('account_type', 'vendor')->count();

        return [
            [
                'label' => 'Annonces actives',
                'value' => number_format($activeListings),
                'icon' => 'heroicon-o-document-text',
                'color' => 'primary',
                'change' => '+12%',
                'changeType' => 'increase',
            ],
            [
                'label' => 'Paiements en attente',
                'value' => number_format($pendingPayments),
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
                'change' => $pendingPayments > 0 ? 'Action requise' : 'Tout traite',
                'changeType' => $pendingPayments > 0 ? 'warning' : 'success',
            ],
            [
                'label' => 'Utilisateurs',
                'value' => number_format($totalUsers),
                'icon' => 'heroicon-o-users',
                'color' => 'success',
                'change' => '+8%',
                'changeType' => 'increase',
            ],
            [
                'label' => 'Revenus total',
                'value' => number_format($totalRevenue) . ' DA',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'info',
                'change' => '+23%',
                'changeType' => 'increase',
            ],
            [
                'label' => 'En vedette',
                'value' => number_format($featuredListings),
                'icon' => 'heroicon-o-star',
                'color' => 'warning',
                'change' => 'Actives',
                'changeType' => 'neutral',
            ],
            [
                'label' => 'Vendeurs',
                'value' => number_format($vendorCount),
                'icon' => 'heroicon-o-building-storefront',
                'color' => 'danger',
                'change' => 'Verifies',
                'changeType' => 'neutral',
            ],
        ];
    }
}
