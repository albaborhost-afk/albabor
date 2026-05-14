<?php

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorListings extends ListRecords
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle annonce'),
        ];
    }
}
