<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingMedia extends Model
{
    protected $appends = [
        'url',
        'thumbnail_url',
    ];

    protected $fillable = [
        'listing_id',
        'path',
        'thumbnail_path',
        'order',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ListingMedia $media) {
            $count = static::where('listing_id', $media->listing_id)->count();
            if ($count >= Listing::MAX_IMAGES) {
                throw new \OverflowException(
                    "Nombre maximum d'images atteint (" . Listing::MAX_IMAGES . ") pour l'annonce #{$media->listing_id}."
                );
            }
        });
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrlAttribute(): string
    {
        return url()->route('listing-media.show', ['media' => $this->id]);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return $this->url;
        }

        return url()->route('listing-media.show', [
            'media' => $this->id,
            'variant' => 'thumb',
        ]);
    }
}
