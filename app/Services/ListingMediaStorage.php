<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Pipeline unique de stockage des photos d'annonce :
 * redimensionnement 1200px + filigrane Albabor + miniature 300px.
 * Utilisé par le site, l'API mobile et le panel admin.
 */
class ListingMediaStorage
{
    /** GD garde l'image décodée en mémoire : largeur × hauteur × 4 octets. */
    private const BYTES_PER_PIXEL = 4;

    /** Marge pour les copies de travail (redimensionnement, filigrane). */
    private const DECODE_OVERHEAD = 1.6;

    /**
     * Côté max auquel Imagick pré-décode avant de passer la main à GD.
     * Confortablement au-dessus des 1200px finaux (rééchantillonnage propre),
     * et assez petit pour que GD n'alloue que ~12 Mo au lieu de ~192 Mo.
     */
    private const PRESCALE_EDGE = 2000;

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

        $source = $this->preScale($source);

        if (! $this->fitsInMemory($source)) {
            Log::warning('Listing image skipped: decoding it would exhaust memory_limit', [
                'listing_id'   => $listing->id,
                'pixels'       => $this->pixelCount($source),
                'memory_limit' => ini_get('memory_limit'),
            ]);

            return null;
        }

        try {
            $img = Image::read($source);
            $img->scaleDown(1200, 1200);

            // La miniature dérive de cette copie déjà réduite. Relire $source
            // décoderait la photo pleine résolution une seconde fois — c'est ce
            // qui saturait memory_limit sur les photos de téléphone 40 Mpx+.
            $downscaled = (string) $img->toJpeg(90);

            app(ListingImageWatermark::class)->apply($img);
            $mainStored = Storage::disk($disk)->put($path, (string) $img->toJpeg(85));
            unset($img);

            if (! $mainStored) {
                Log::error('Storage::put returned false for listing image', [
                    'path' => $path, 'disk' => $disk,
                ]);

                return null;
            }

            $thumb = Image::read($downscaled);
            $thumb->cover(300, 300);
            app(ListingImageWatermark::class)->apply($thumb);
            $thumbStored = Storage::disk($disk)->put($thumbPath, (string) $thumb->toJpeg(75));
            unset($thumb, $downscaled);

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
        } finally {
            // Sans cela, les bitmaps de l'image précédente restent alloués
            // pendant le décodage de la suivante.
            gc_collect_cycles();
        }
    }

    /**
     * Décode la photo à résolution réduite AVANT de la confier à GD.
     *
     * GD décompresse toujours l'image entière : une photo de téléphone 48 Mpx
     * fait 8000 × 6000 × 4 = 192 Mo, ce qui crève memory_limit (256 Mo) par une
     * erreur FATALE — qu'aucun try/catch ne rattrape, et l'annonce entière est
     * perdue. C'est la cause des 500 sur POST /annonces.
     *
     * Imagick sait demander à libjpeg un décodage déjà réduit (jpeg:size) : la
     * pleine résolution n'est jamais allouée. Mesuré sur une photo 48 Mpx,
     * pic mémoire réel du processus :
     *
     *   GD seul .............................. 232 Mo  → dépasse memory_limit
     *   pilote Intervention Imagick .......... 633 Mo  → l'OOM killer tue le conteneur (512 Mio)
     *   Imagick + jpeg:size (ci-dessous) ..... 123 Mo  → sûr des deux côtés
     *
     * On ne remplace donc QUE l'étape de décodage. Filigrane et miniature
     * restent sur GD : le rendu des photos ne change pas.
     *
     * Retourne un JPEG (binaire) réduit, ou la source inchangée si Imagick est
     * absent ou échoue — auquel cas fitsInMemory() reste le garde-fou.
     */
    private function preScale(mixed $source): mixed
    {
        $path = $this->realPath($source);

        if ($path === null || ! class_exists(\Imagick::class)) {
            return $source;
        }

        try {
            // Au-delà de ces limites ImageMagick bascule sur un cache disque
            // au lieu de dévorer la RAM du conteneur.
            \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, 128 * 1024 * 1024);
            \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MAP, 256 * 1024 * 1024);

            $imagick = new \Imagick();
            $imagick->setOption('jpeg:size', self::PRESCALE_EDGE . 'x' . self::PRESCALE_EDGE);
            $imagick->readImage($path);

            // Grave la rotation EXIF dans les pixels : le ré-encodage ci-dessous
            // perd la balise, GD ne pivoterait plus rien.
            $imagick->autoOrient();

            if ($imagick->getImageWidth() > self::PRESCALE_EDGE || $imagick->getImageHeight() > self::PRESCALE_EDGE) {
                $imagick->thumbnailImage(self::PRESCALE_EDGE, self::PRESCALE_EDGE, true);
            }

            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(92);
            $blob = $imagick->getImageBlob();

            $imagick->clear();
            $imagick->destroy();

            return $blob;
        } catch (\Throwable $e) {
            report($e);

            return $source;
        }
    }

    private function realPath(mixed $source): ?string
    {
        return match (true) {
            $source instanceof UploadedFile        => $source->getRealPath() ?: null,
            is_string($source) && @is_file($source) => $source,
            default                                => null,
        };
    }

    /**
     * Saturer memory_limit est une erreur FATALE : aucun try/catch ne la
     * rattrape et toute la soumission de l'annonce est perdue. On décide donc
     * *avant* de décoder si l'image tient, quitte à sauter cette photo.
     */
    private function fitsInMemory(mixed $source): bool
    {
        $pixels = $this->pixelCount($source);

        if ($pixels === null) {
            return true; // Source non mesurable : Image::read() lèvera une exception rattrapable.
        }

        $limit = $this->memoryLimitInBytes();

        if ($limit < 0) {
            return true; // Pas de limite.
        }

        $needed = (int) ($pixels * self::BYTES_PER_PIXEL * self::DECODE_OVERHEAD);

        return memory_get_usage(true) + $needed <= $limit;
    }

    private function pixelCount(mixed $source): ?int
    {
        $path = $this->realPath($source);

        if ($path === null) {
            return null;
        }

        $info = @getimagesize($path);

        return $info === false ? null : $info[0] * $info[1];
    }

    private function memoryLimitInBytes(): int
    {
        $raw = trim((string) ini_get('memory_limit'));

        if ($raw === '' || $raw === '-1') {
            return -1;
        }

        $value = (int) $raw;

        return match (strtolower(substr($raw, -1))) {
            'g'     => $value * 1024 ** 3,
            'm'     => $value * 1024 ** 2,
            'k'     => $value * 1024,
            default => $value,
        };
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
