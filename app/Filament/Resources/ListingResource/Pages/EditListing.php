<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Filament only submits visible, validated fields. Replacing the JSON
        // would erase fields maintained by the website/apps or hidden sections.
        $specs = $this->getRecord()->specs ?? [];

        foreach ($data['specs'] ?? [] as $section => $fields) {
            // Each section contains field values. Replace those values atomically,
            // including empty tag lists, null, zero and false (intentional edits).
            $specs[$section] = is_array($fields) && is_array($specs[$section] ?? null)
                ? array_replace($specs[$section], $fields)
                : $fields;
        }

        $data['specs'] = $specs;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
