<?php

namespace App\Filament\Support;

use App\Exceptions\ListingOwnershipException;
use App\Models\Listing;
use App\Models\User;
use App\Services\ListingOwnership;
use Filament\Actions\MountableAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * L'action « Transférer à un autre compte », identique dans la liste des
 * annonces, en haut de la page de modification et en action groupée.
 */
final class TransferListingAction
{
    private const DESCRIPTION = 'L\'annonce change de propriétaire : elle apparaît dans « Mes annonces » du nouveau compte, '
        .'avec les conversations et les demandes de médiation qui la concernent. '
        .'Les paiements déjà enregistrés restent au nom du compte qui a payé, et les coordonnées affichées '
        .'sur l\'annonce (WhatsApp, mobile, e-mail) ne sont pas modifiées.';

    /**
     * Une annonce à la fois — action de ligne ou d'en-tête de page.
     *
     * @template T of MountableAction
     * @param  T  $action
     * @return T
     */
    public static function configure(MountableAction $action): MountableAction
    {
        return $action
            ->label('Transférer à un autre compte')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->modalIcon('heroicon-o-arrow-right-circle')
            ->modalHeading(fn (Listing $record): string => 'Transférer « '.Str::limit($record->title, 60).' »')
            ->modalDescription(self::DESCRIPTION)
            ->modalSubmitActionLabel('Transférer l\'annonce')
            ->form([
                Forms\Components\Placeholder::make('current_owner')
                    ->label('Propriétaire actuel')
                    ->content(fn (Listing $record): string => $record->user
                        ? ListingOwnerSelect::label($record->user)
                        : '—'),
                ListingOwnerSelect::make('new_owner_id')
                    ->label('Nouveau propriétaire'),
            ])
            ->action(function (Listing $record, array $data): void {
                $newOwner = User::findOrFail($data['new_owner_id']);

                if ((int) $record->user_id === (int) $newOwner->id) {
                    Notification::make()
                        ->title('Aucun changement')
                        ->body('Ce compte est déjà propriétaire de l\'annonce.')
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    app(ListingOwnership::class)->transfer($record, $newOwner, auth()->user());
                } catch (ListingOwnershipException $e) {
                    Notification::make()
                        ->title('Transfert impossible')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Annonce transférée')
                    ->body('« '.Str::limit($record->title, 60).' » appartient maintenant à '.$newOwner->real_name.'.')
                    ->success()
                    ->send();
            });
    }

    /** Plusieurs annonces vers le même compte — action groupée de la liste. */
    public static function configureBulk(BulkAction $action): BulkAction
    {
        return $action
            ->label('Transférer la sélection à un compte')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->modalIcon('heroicon-o-arrow-right-circle')
            ->modalHeading('Transférer les annonces sélectionnées')
            ->modalDescription(self::DESCRIPTION)
            ->modalSubmitActionLabel('Transférer')
            ->form([
                ListingOwnerSelect::make('new_owner_id')
                    ->label('Nouveau propriétaire'),
            ])
            ->action(function (Collection $records, array $data): void {
                $newOwner = User::findOrFail($data['new_owner_id']);
                $service  = app(ListingOwnership::class);
                $actor    = auth()->user();

                $transferred = 0;
                $unchanged   = 0;

                try {
                    foreach ($records as $listing) {
                        if ((int) $listing->user_id === (int) $newOwner->id) {
                            $unchanged++;

                            continue;
                        }

                        $service->transfer($listing, $newOwner, $actor);
                        $transferred++;
                    }
                } catch (ListingOwnershipException $e) {
                    Notification::make()
                        ->title('Transfert impossible')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $body = $transferred.' annonce(s) transférée(s) à '.$newOwner->real_name.'.';

                if ($unchanged > 0) {
                    $body .= ' '.$unchanged.' appartenai(en)t déjà à ce compte.';
                }

                Notification::make()
                    ->title('Transfert terminé')
                    ->body($body)
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
