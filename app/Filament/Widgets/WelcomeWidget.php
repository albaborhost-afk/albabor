<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Bonjour';
        } elseif ($hour < 18) {
            return 'Bon apres-midi';
        } else {
            return 'Bonsoir';
        }
    }

    public function getUserName(): string
    {
        return auth()->user()?->name ?? 'Admin';
    }

    public function getTodayDate(): string
    {
        return now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
    }
}
