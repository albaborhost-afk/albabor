<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Intervention\Image\Interfaces\ImageInterface;

class ListingImageWatermark
{
    /**
     * Apply Albabor logo watermark to an image (modifies in place).
     * Watermark is placed center-bottom by default.
     * Safe to call if watermark file is missing; then no change is made.
     */
    public function apply(ImageInterface $image): void
    {
        $path = config('listings.watermark_path');
        if (!$path || !File::isFile($path)) {
            return;
        }

        try {
            $watermark = \Intervention\Image\Laravel\Facades\Image::read($path);

            $imgWidth  = $image->width();
            $imgHeight = $image->height();

            // Scale watermark to configured fraction of image width (min 80px)
            $maxW = (int) round($imgWidth * config('listings.watermark_max_width_ratio', 0.30));
            if ($maxW < 80) {
                $maxW = 80;
            }
            $watermark->scaleDown($maxW, 9999);

            // Save watermark to a temp file for placement
            $tmpFile = tempnam(sys_get_temp_dir(), 'albabor_wm_');
            if ($tmpFile === false) {
                return;
            }
            $tmpPng = $tmpFile . '.png';
            rename($tmpFile, $tmpPng);

            $watermark->toPng()->save($tmpPng);

            $image->place(
                $tmpPng,
                config('listings.watermark_position', 'bottom'),
                config('listings.watermark_offset_x', 0),
                config('listings.watermark_offset_y', 30),
                config('listings.watermark_opacity', 30)
            );

            @unlink($tmpPng);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Apply watermark to an image read from a storage path.
     * Returns the watermarked image as JPEG binary string, or null on failure.
     */
    public function applyToStoredImage(string $disk, string $path, int $quality = 85): ?string
    {
        try {
            $contents = \Illuminate\Support\Facades\Storage::disk($disk)->get($path);
            if (!$contents) {
                return null;
            }

            $image = \Intervention\Image\Laravel\Facades\Image::read($contents);
            $this->apply($image);

            return (string) $image->toJpeg($quality);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
