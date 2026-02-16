<?php

namespace App\Filament\Resources\VerificationRequestResource\Pages;

use App\Filament\Resources\VerificationRequestResource;
use App\Models\VerificationRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditVerificationRequest extends EditRecord
{
    protected static string $resource = VerificationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approuver la vérification')
                ->modalDescription('Voulez-vous vraiment approuver cette demande de vérification ? L\'utilisateur recevra le badge vérifié.')
                ->modalSubmitActionLabel('Oui, approuver')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                        'rejection_reason' => null,
                    ]);

                    $this->record->user->update([
                        'verified_badge' => true,
                        'verification_status' => 'approved',
                    ]);

                    Notification::make()
                        ->title('Demande approuvée')
                        ->body('L\'utilisateur ' . $this->record->user->name . ' a reçu le badge vérifié.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Actions\Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rejeter la vérification')
                ->modalDescription('Veuillez indiquer la raison du rejet.')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Raison du rejet')
                        ->required()
                        ->maxLength(1000),
                ])
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $this->record->user->update([
                        'verified_badge' => false,
                        'verification_status' => 'rejected',
                    ]);

                    Notification::make()
                        ->title('Demande rejetée')
                        ->body('La demande de ' . $this->record->user->name . ' a été rejetée.')
                        ->danger()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
