<?php

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorListing extends EditRecord
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Supprimer'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
