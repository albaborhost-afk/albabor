<?php

namespace App\Filament\Resources\BannerRequestResource\Pages;

use App\Filament\Resources\BannerRequestResource;
use App\Models\BannerRequest;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerRequest extends EditRecord
{
    protected static string $resource = BannerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Rappeler l'annonceur est l'action principale : elle doit être
            // à portée de main depuis la fiche, pas seulement dans la liste.
            Actions\Action::make('whatsapp')
                ->label('Ouvrir WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn (BannerRequest $record): string => $record->whatsapp_url)
                ->openUrlInNewTab(),

            Actions\Action::make('email')
                ->label('Envoyer un e-mail')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(fn (BannerRequest $record): string => 'mailto:' . $record->email),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
