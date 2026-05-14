<?php

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorListing extends CreateRecord
{
    protected static string $resource = VendorListingResource::class;

    /**
     * Force le propriétaire et le statut "en attente de validation".
     * La modération admin reste obligatoire avant publication.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending_review';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
