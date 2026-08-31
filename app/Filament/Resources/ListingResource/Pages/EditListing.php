<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use App\Filament\Support\TransferListingAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Seul chemin pour changer le vendeur : le champ du formulaire est
            // en lecture seule (voir ListingResource::form).
            TransferListingAction::configure(Actions\Action::make('transfer')),
            Actions\DeleteAction::make(),
        ];
    }
}
