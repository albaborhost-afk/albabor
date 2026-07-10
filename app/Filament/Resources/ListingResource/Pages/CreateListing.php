<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use App\Models\Listing;
use App\Services\ListingMediaStorage;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;

    protected function afterCreate(): void
    {
        // Enforce the image limit
        $uploadedFiles = array_slice($this->data['new_images'] ?? [], 0, Listing::MAX_IMAGES);

        if (empty($uploadedFiles)) {
            return;
        }

        $storage = app(ListingMediaStorage::class);
        $saved = 0;

        foreach ($uploadedFiles as $tmpPath) {
            if (!$tmpPath || !Storage::disk('local')->exists($tmpPath)) {
                continue;
            }

            if ($storage->store($this->record, Storage::disk('local')->path($tmpPath))) {
                $saved++;
            }

            // Clean up temp file
            Storage::disk('local')->delete($tmpPath);
        }

        if ($saved > 0) {
            Notification::make()
                ->title($saved . ' image(s) ajoutée(s)')
                ->success()
                ->send();
        }
    }
}
