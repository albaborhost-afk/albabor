<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'description',
        'logo_path',
        'banner_path',
        'contact_phone',
        'contact_email',
        'wilaya',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Génère un slug unique à partir du nom de la boutique si absent.
        static::saving(function (VendorProfile $profile) {
            if (blank($profile->slug) && filled($profile->shop_name)) {
                $profile->slug = static::uniqueSlug($profile->shop_name, $profile->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'boutique';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Annonces pièces / moteurs publiées par le vendeur propriétaire.
     */
    public function listings()
    {
        return $this->hasMany(Listing::class, 'user_id', 'user_id')
            ->whereIn('category', ['engine', 'parts']);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? \Illuminate\Support\Facades\Storage::disk(config('filesystems.listing_disk', 'public'))->url($this->logo_path)
            : null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path
            ? \Illuminate\Support\Facades\Storage::disk(config('filesystems.listing_disk', 'public'))->url($this->banner_path)
            : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
