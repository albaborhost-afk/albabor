<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'link_url',
        'company_name',
        'position',
        'is_active',
        'starts_at',
        'ends_at',
        'click_count',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'position' => 'integer',
            'click_count' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('position', 'asc')
            ->orderBy('id', 'desc');
    }

    /**
     * Compte une diffusion pour chaque bannière affichée.
     *
     * Le site n'incrémentait rien — seule l'application mobile comptait, si
     * bien que « vues » sous-estimait fortement ce que l'annonceur a payé.
     * Une seule requête, quel que soit le nombre de bannières.
     */
    public static function recordImpressions(iterable $banners): void
    {
        $ids = collect($banners)->pluck('id')->filter()->all();

        if (empty($ids)) {
            return;
        }

        static::whereIn('id', $ids)->increment('view_count');
    }

    /** Taux de clic, en pourcentage des diffusions. */
    public function getClickThroughRateAttribute(): float
    {
        if (! $this->view_count) {
            return 0.0;
        }

        return round(($this->click_count / $this->view_count) * 100, 2);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk(config('filesystems.listing_disk', 'public'))->url($this->image_path);
    }
}
