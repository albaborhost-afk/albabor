<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Approuver le paiement')
                ->modalDescription("Êtes-vous sûr de vouloir approuver ce paiement ? Cette action est irréversible.")
                ->modalSubmitActionLabel('Oui, approuver')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    // Handle listing publish payment
                    if ($this->record->type === 'publish_listing' && $this->record->listing) {
                        $updateData = ['published_until' => now()->addYear()];
                        if ($this->record->listing->status === 'awaiting_payment') {
                            $updateData['status'] = 'pending_review';
                        }
                        $this->record->listing->update($updateData);
                    }

                    // Handle featured payment
                    if ($this->record->type === 'featured_listing' && $this->record->listing) {
                        $this->record->listing->update(['featured_until' => now()->addDays(30)]);
                    }

                    // Handle subscription payment
                    if ($this->record->type === 'vendor_subscription' && $this->record->subscription) {
                        $this->record->subscription->update([
                            'status' => 'active',
                            'starts_at' => now(),
                            'ends_at' => now()->addDays($this->record->subscription->plan->duration_days ?? 30),
                        ]);
                    }

                    // Handle mediation fee payment
                    if ($this->record->type === 'mediation_fee' && $this->record->mediationTicket) {
                        $this->record->mediationTicket->update(['payment_status' => 'paid']);
                    }

                    Notification::make()
                        ->title("Paiement approuvé")
                        ->body("Le paiement a été approuvé avec succès.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'approved_by', 'approved_at']);
                }),

            Actions\Action::make('reject')
                ->label('Refuser')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->modalHeading('Refuser le paiement')
                ->modalDescription('Veuillez indiquer la raison du refus.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Raison du refus')
                        ->placeholder('Ex: Justificatif illisible, montant incorrect...')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title("Paiement refusé")
                        ->body("Le paiement a été refusé.")
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),
        ];
    }
}
