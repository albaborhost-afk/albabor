<?php

namespace App\Http\Controllers;

use App\Models\ListingMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ListingMediaController extends Controller
{
    /** Durée de validité d'un lien signé S3. */
    private const SIGNED_URL_TTL_HOURS = 6;

    public function show(ListingMedia $media, ?string $variant = null): Response | RedirectResponse
    {
        if (!in_array($variant, [null, 'thumb'], true)) {
            abort(404);
        }

        $listing = $media->listing;
        if (!$listing) {
            abort(404);
        }

        // Non-active listings only visible to owner or admin
        if ($listing->status !== 'active') {
            $user = auth()->user();
            if (!$user || ($user->id !== $listing->user_id && !$user->isAdmin())) {
                abort(404);
            }
        }

        $path = ($variant === 'thumb' && $media->thumbnail_path)
            ? $media->thumbnail_path
            : $media->path;

        if (!is_string($path) || $path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $diskName = config('filesystems.listing_disk', 'public');
        $disk     = Storage::disk($diskName);

        // Sur S3, on renvoie un lien signé et le navigateur télécharge
        // directement depuis le stockage.
        //
        // Auparavant chaque vignette traversait PHP : exists() + mimeType() +
        // get(), soit trois allers-retours S3 par image, plus le fichier entier
        // chargé en mémoire dans un processus FPM. Une page de résultats avec
        // 25 annonces mobilisait donc des dizaines de processus bloqués sur le
        // réseau — d'où « pool www seems busy » et les « upstream timed out »
        // sur /media/listings/…
        //
        // Le contrôle d'accès reste fait ici, avant la redirection ; le lien
        // signé expire au bout de quelques heures.
        if (config("filesystems.disks.{$diskName}.driver") === 's3') {
            try {
                return redirect()->away(
                    $disk->temporaryUrl($path, now()->addHours(self::SIGNED_URL_TTL_HOURS)),
                    302,
                    // Plus court que la signature : un navigateur ne doit jamais
                    // garder en cache une redirection déjà expirée.
                    ['Cache-Control' => 'private, max-age=3600'],
                );
            } catch (\Throwable $e) {
                Log::warning('Signed media URL unavailable, falling back to streaming', [
                    'media_id' => $media->id,
                    'path'     => $path,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // Disque local, ou S3 momentanément incapable de signer.
        try {
            $content = $disk->get($path);
        } catch (\Throwable $e) {
            Log::warning('Could not serve listing media', [
                'media_id' => $media->id,
                'path'     => $path,
                'error'    => $e->getMessage(),
            ]);
            abort(404);
        }

        if ($content === null) {
            abort(404);
        }

        return response($content, 200, [
            // Déduit de l'extension : demander le type au stockage coûtait un
            // aller-retour réseau supplémentaire pour une information connue.
            'Content-Type'  => $this->mimeTypeFor($path),
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }

    private function mimeTypeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'          => 'image/png',
            'webp'         => 'image/webp',
            'gif'          => 'image/gif',
            'heic', 'heif' => 'image/heic',
            default        => 'image/jpeg',
        };
    }
}
