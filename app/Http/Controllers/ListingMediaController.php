<?php

namespace App\Http\Controllers;

use App\Models\ListingMedia;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ListingMediaController extends Controller
{
    public function show(ListingMedia $media, ?string $variant = null): Response
    {
        if (!in_array($variant, [null, 'thumb'], true)) {
            abort(404);
        }

        $listing = $media->listing;
        if (!$listing) {
            abort(404);
        }

        if ($listing->status !== 'active') {
            if (!auth()->check() || auth()->id() !== $listing->user_id) {
                abort(404);
            }
        }

        $path = ($variant === 'thumb' && $media->thumbnail_path)
            ? $media->thumbnail_path
            : $media->path;

        if (!is_string($path) || $path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return response($disk->get($path), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
