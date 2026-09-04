<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingView extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id',
        'ip_hash',
        'view_date',
    ];

    protected function casts(): array
    {
        return [
            'view_date' => 'date',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une vue par annonce, par visiteur et par jour.
     *
     * Recherche par whereDate : la colonne est castée `date` et SQLite la
     * stocke « 2026-09-03 00:00:00 » — une égalité avec « 2026-09-03 » ne la
     * retrouvait pas, l'insertion suivante violait l'index unique et la page
     * répondait 500 dès la deuxième visite du jour. Le même index peut aussi
     * être violé par deux premières visites simultanées : on l'attrape.
     */
    public static function recordView(Listing $listing, ?User $user, string $ip): bool
    {
        $ipHash = hash('sha256', $ip);
        $today = now()->toDateString();

        $alreadyViewed = static::query()
            ->where('listing_id', $listing->id)
            ->where('ip_hash', $ipHash)
            ->whereDate('view_date', $today)
            ->exists();

        if ($alreadyViewed) {
            return false;
        }

        try {
            static::create([
                'listing_id' => $listing->id,
                'ip_hash' => $ipHash,
                'view_date' => $today,
                'user_id' => $user?->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        $listing->increment('views_count');

        return true;
    }
}
