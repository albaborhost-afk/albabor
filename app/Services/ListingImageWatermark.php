<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Intervention\Image\Interfaces\ImageInterface;

class ListingImageWatermark
{
    /**
     * Filigranes déjà préparés pour cette requête, indexés par largeur cible.
     * Sans ce cache, le logo était relu et ré-encodé en PNG pour chaque photo
     * ET chaque miniature — 40 allers-retours disque pour une annonce de 20 photos.
     *
     * @var array<int, string>
     */
    private static array $prepared = [];

    private static bool $cleanupRegistered = false;

    /**
     * Apply Albabor logo watermark to an image (modifies in place).
     * Watermark is placed center-bottom by default.
     * Safe to call if watermark file is missing; then no change is made.
     */
    public function apply(ImageInterface $image): void
    {
        $source = config('listings.watermark_path');

        if (! $source || ! File::isFile($source)) {
            return;
        }

        try {
            // Largeur du filigrane : fraction configurée de la largeur de l'image (min 80px).
            $width = (int) round($image->width() * config('listings.watermark_max_width_ratio', 0.30));
            $width = max($width, 80);

            $watermark = $this->prepare($source, $width);

            if ($watermark === null) {
                return;
            }

            $image->place(
                $watermark,
                config('listings.watermark_position', 'bottom'),
                config('listings.watermark_offset_x', 0),
                config('listings.watermark_offset_y', 30),
                config('listings.watermark_opacity', 30)
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Rend le logo à la largeur demandée dans un PNG temporaire, réutilisé
     * pour toutes les images de la requête qui partagent cette largeur.
     */
    private function prepare(string $source, int $width): ?string
    {
        if (isset(self::$prepared[$width]) && is_file(self::$prepared[$width])) {
            return self::$prepared[$width];
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'albabor_wm_');

        if ($tmpFile === false) {
            return null;
        }

        $tmpPng = $tmpFile . '.png';
        rename($tmpFile, $tmpPng);

        $watermark = \Intervention\Image\Laravel\Facades\Image::read($source);
        $watermark->scaleDown($width, 9999);
        $watermark->toPng()->save($tmpPng);
        unset($watermark);

        self::$prepared[$width] = $tmpPng;
        self::registerCleanup();

        return $tmpPng;
    }

    private static function registerCleanup(): void
    {
        if (self::$cleanupRegistered) {
            return;
        }

        self::$cleanupRegistered = true;

        register_shutdown_function(static function (): void {
            foreach (self::$prepared as $path) {
                @unlink($path);
            }

            self::$prepared = [];
        });
    }

    /**
     * Apply watermark to an image read from a storage path.
     * Returns the watermarked image as JPEG binary string, or null on failure.
     */
    public function applyToStoredImage(string $disk, string $path, int $quality = 85): ?string
    {
        try {
            $contents = \Illuminate\Support\Facades\Storage::disk($disk)->get($path);

            if (! $contents) {
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
