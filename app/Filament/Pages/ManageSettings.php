<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Parametres';
    protected static ?string $title = 'Parametres de la plateforme';
    protected static ?string $navigationGroup = 'Gestion';
    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.manage-settings';

    public ?string $exchange_rate_eur_dzd = '';

    public function mount(): void
    {
        $this->exchange_rate_eur_dzd = Setting::get('exchange_rate_eur_dzd', '238.00');
    }

    public function save(): void
    {
        $rate = (float) $this->exchange_rate_eur_dzd;

        if ($rate <= 0) {
            Notification::make()
                ->title('Erreur')
                ->body('Le taux de change doit etre superieur a 0.')
                ->danger()
                ->send();
            return;
        }

        Setting::set('exchange_rate_eur_dzd', number_format($rate, 2, '.', ''));

        Notification::make()
            ->title('Parametres enregistres')
            ->body('Le taux de change a ete mis a jour avec succes.')
            ->success()
            ->send();
    }
}
