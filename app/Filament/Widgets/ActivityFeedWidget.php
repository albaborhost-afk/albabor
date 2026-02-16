<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ActivityFeedWidget extends Widget
{
    protected static string $view = 'filament.widgets.activity-feed-widget';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public function getRecentActivities(): Collection
    {
        $activities = collect();

        // Recent users
        User::latest()->take(3)->get()->each(function ($user) use ($activities) {
            $activities->push([
                'type' => 'user',
                'icon' => 'heroicon-o-user-plus',
                'color' => 'blue',
                'title' => 'Nouvel utilisateur',
                'description' => $user->name,
                'time' => $user->created_at,
            ]);
        });

        // Recent listings
        Listing::latest()->take(3)->get()->each(function ($listing) use ($activities) {
            $activities->push([
                'type' => 'listing',
                'icon' => 'heroicon-o-document-text',
                'color' => 'purple',
                'title' => 'Nouvelle annonce',
                'description' => \Str::limit($listing->title, 30),
                'time' => $listing->created_at,
            ]);
        });

        // Recent payments
        Payment::latest()->take(3)->get()->each(function ($payment) use ($activities) {
            $status = match ($payment->status) {
                'approved' => ['text' => 'Paiement approuve', 'color' => 'green'],
                'pending' => ['text' => 'Nouveau paiement', 'color' => 'amber'],
                'rejected' => ['text' => 'Paiement refuse', 'color' => 'red'],
                default => ['text' => 'Paiement', 'color' => 'gray'],
            };

            $activities->push([
                'type' => 'payment',
                'icon' => 'heroicon-o-credit-card',
                'color' => $status['color'],
                'title' => $status['text'],
                'description' => number_format($payment->amount_dzd) . ' DA',
                'time' => $payment->created_at,
            ]);
        });

        return $activities->sortByDesc('time')->take(8);
    }
}
