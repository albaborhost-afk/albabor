<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use App\Models\ListingMedia;
use App\Services\ListingImageWatermark;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;

    protected function afterCreate(): void
    {
        $uploadedFiles = $this->data['new_images'] ?? [];

        if (empty($uploadedFiles)) {
            return;
        }

        $listing = $this->record;
        $disk = config('filesystems.listing_disk', 'public');
        $order = 0;
        $saved = 0;
        $max = \App\Models\Listing::MAX_IMAGES;

        // Enforce the image limit
        $uploadedFiles = array_slice($uploadedFiles, 0, $max);

        foreach ($uploadedFiles as $tmpPath) {
            if (!$tmpPath || !Storage::disk('local')->exists($tmpPath)) {
                continue;
            }

            $order++;
            $filename  = uniqid('img_', true) . '.jpg';
            $path      = 'listings/' . $listing->id . '/' . $filename;
            $thumbPath = 'listings/' . $listing->id . '/thumb_' . $filename;

            try {
                $fullPath = Storage::disk('local')->path($tmpPath);

                // Resize main image (max 1200px), apply Albabor watermark
                $img = Image::read($fullPath);
                $img->scaleDown(1200, 1200);
                app(ListingImageWatermark::class)->apply($img);
                Storage::disk($disk)->put($path, (string) $img->toJpeg(85));

                // Create thumbnail (300px) with watermark
                $thumb = Image::read($fullPath);
                $thumb->cover(300, 300);
                app(ListingImageWatermark::class)->apply($thumb);
                $thumbStored = Storage::disk($disk)->put($thumbPath, (string) $thumb->toJpeg(75));

                ListingMedia::create([
                    'listing_id'     => $listing->id,
                    'path'           => $path,
                    'thumbnail_path' => $thumbStored ? $thumbPath : null,
                    'order'          => $order,
                ]);

                $saved++;

                // Clean up temp file
                Storage::disk('local')->delete($tmpPath);
            } catch (\Throwable $e) {
                \Log::error('Filament image upload failed', [
                    'listing_id' => $listing->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        if ($saved > 0) {
            Notification::make()
                ->title($saved . ' image(s) ajoutée(s)')
                ->success()
                ->send();
        }
    }
}
