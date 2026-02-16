<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\Payment;
use App\Models\VerificationRequest;
use Filament\Widgets\Widget;

class PendingActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.pending-actions-widget';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    public function getPendingListingsCount(): int
    {
        return Listing::where('status', 'pending_review')->count();
    }

    public function getPendingPaymentsCount(): int
    {
        return Payment::where('status', 'pending')->count();
    }

    public function getPendingVerificationsCount(): int
    {
        return VerificationRequest::where('status', 'pending')->count();
    }

    public function getActiveMediationsCount(): int
    {
        return MediationTicket::where('status', 'in_progress')->count();
    }

    public function getListingsUrl(): string
    {
        return route('filament.admin.resources.listings.index', ['tableFilters[status][value]' => 'pending_review']);
    }

    public function getPaymentsUrl(): string
    {
        return route('filament.admin.resources.payments.index', ['tableFilters[status][value]' => 'pending']);
    }

    public function getVerificationsUrl(): string
    {
        return route('filament.admin.resources.verification-requests.index', ['tableFilters[status][value]' => 'pending']);
    }

    public function getMediationsUrl(): string
    {
        return route('filament.admin.resources.mediation-tickets.index', ['tableFilters[status][value]' => 'in_progress']);
    }
}
