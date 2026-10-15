<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['hide_name'] = $this->getRecord()->user?->hasPrivateProfile() ?? false;

        return $data;
    }

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $hideName = array_key_exists('hide_name', $data) ? (bool) $data['hide_name'] : null;
        unset($data['hide_name']);

        return DB::transaction(function () use ($record, $data, $hideName): Model {
            $record = parent::handleRecordUpdate($record, $data);

            if ($hideName !== null) {
                // Query after saving: the administrator may have changed the seller.
                $record->user()->first()?->update(['hide_name' => $hideName]);
            }

            return $record;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
