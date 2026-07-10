<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Pipeline unique de stockage des photos d'annonce :
 * redimensionnement 1200px + filigrane Albabor + miniature 300px.
 * Utilisé par le panel admin (création, édition, gestion des photos).
 */
class ListingMediaStorage
{
    /**
     * Traite une image source (chemin absolu ou fichier uploadé) et l'attache
     * à l'annonce. Retourne null (et journalise) en cas d'échec.
     *
     * @param mixed $source tout ce que Image::read() accepte (chemin, UploadedFile…)
     */
    public function store(Listing $listing, mixed $source, ?int $order = null): ?ListingMedia
    {
        $disk      = config('filesystems.listing_disk', 'public');
        $order     = $order ?? (($listing->media()->max('order') ?? 0) + 1);
        $filename  = uniqid('img_', true) . '.jpg';
        $path      = 'listings/' . $listing->id . '/' . $filename;
        $thumbPath = 'listings/' . $listing->id . '/thumb_' . $filename;

        try {
            // Resize main image (max 1200px), apply Albabor watermark
            $img = Image::read($source);
            $img->scaleDown(1200, 1200);
            app(ListingImageWatermark::class)->apply($img);

            if (!Storage::disk($disk)->put($path, (string) $img->toJpeg(85))) {
                Log::error('Storage::put returned false for listing image', [
                    'path' => $path, 'disk' => $disk,
                ]);
                return null;
            }

            // Create thumbnail (300px) with watermark
            $thumb = Image::read($source);
            $thumb->cover(300, 300);
            app(ListingImageWatermark::class)->apply($thumb);
            $thumbStored = Storage::disk($disk)->put($thumbPath, (string) $thumb->toJpeg(75));

            return ListingMedia::create([
                'listing_id'     => $listing->id,
                'path'           => $path,
                'thumbnail_path' => $thumbStored ? $thumbPath : null,
                'order'          => $order,
            ]);
        } catch (\Throwable $e) {
            Log::error('Exception while storing listing image', [
                'listing_id' => $listing->id,
                'path'       => $path,
                'disk'       => $disk,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Supprime les fichiers (image + miniature) du disque, puis la ligne.
     */
    public function delete(ListingMedia $media): void
    {
        $disk = Storage::disk(config('filesystems.listing_disk', 'public'));

        try {
            $disk->delete($media->path);
            if ($media->thumbnail_path) {
                $disk->delete($media->thumbnail_path);
            }
        } catch (\Throwable) {
            // Un échec de nettoyage (ex. S3 indisponible) ne doit pas bloquer la suppression.
        }

        $media->delete();
    }

    /**
     * Place cette photo en couverture (première position) et reséquence 1..n.
     */
    public function moveToFront(ListingMedia $media): void
    {
        $media->update(['order' => 1]);

        $position = 1;
        $media->listing->media()
            ->whereKeyNot($media->getKey())
            ->orderBy('order')
            ->get()
            ->each(fn (ListingMedia $item) => $item->update(['order' => ++$position]));
    }

    /**
     * Nombre d'emplacements photo encore disponibles pour l'annonce.
     */
    public function slotsLeft(Listing $listing): int
    {
        return max(0, Listing::MAX_IMAGES - $listing->media()->count());
    }
}
