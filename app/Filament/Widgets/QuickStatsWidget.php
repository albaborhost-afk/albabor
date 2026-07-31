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

        // Variations réelles sur 30 jours. Ces trois cartes affichaient des
        // pourcentages écrits en dur (+12 %, +8 %, +23 %) : ils ne bougeaient
        // jamais et donnaient une fausse impression de croissance.
        $listingsChange = $this->monthlyChange(Listing::query());
        $usersChange    = $this->monthlyChange(User::query());
        $revenueChange  = $this->monthlyChange(
            Payment::where('status', 'approved'),
            'amount_dzd'
        );

        return [
            [
                'label' => 'Annonces actives',
                'value' => number_format($activeListings),
                'icon' => 'heroicon-o-document-text',
                'color' => 'primary',
                'change' => $listingsChange['label'],
                'changeType' => $listingsChange['type'],
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
                'change' => $usersChange['label'],
                'changeType' => $usersChange['type'],
            ],
            [
                'label' => 'Revenus total',
                'value' => number_format($totalRevenue) . ' DA',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'info',
                'change' => $revenueChange['label'],
                'changeType' => $revenueChange['type'],
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

    /**
     * Variation sur 30 jours : les 30 derniers jours comparés aux 30 d'avant.
     *
     * @param  string|null  $sumColumn  colonne à sommer, ou null pour compter les lignes
     * @return array{label: string, type: string}
     */
    private function monthlyChange($query, ?string $sumColumn = null): array
    {
        $measure = function ($from, $to) use ($query, $sumColumn) {
            $scoped = (clone $query)->whereBetween('created_at', [$from, $to]);

            return (int) ($sumColumn ? $scoped->sum($sumColumn) : $scoped->count());
        };

        $current  = $measure(now()->subDays(30), now());
        $previous = $measure(now()->subDays(60), now()->subDays(30));

        if ($previous === 0) {
            return $current > 0
                ? ['label' => 'Nouveau ce mois', 'type' => 'increase']
                : ['label' => 'Aucun ce mois', 'type' => 'neutral'];
        }

        $delta = (int) round((($current - $previous) / $previous) * 100);

        return [
            'label' => ($delta > 0 ? '+' : '') . $delta . '% / 30 j',
            'type'  => match (true) {
                $delta > 0 => 'increase',
                $delta < 0 => 'decrease',
                default    => 'neutral',
            },
        ];
    }
}
