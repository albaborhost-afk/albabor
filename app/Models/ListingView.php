<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public static function recordView(Listing $listing, ?User $user, string $ip): bool
    {
        $ipHash = hash('sha256', $ip);
        $today = now()->toDateString();

        $view = static::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'ip_hash' => $ipHash,
                'view_date' => $today,
            ],
            [
                'user_id' => $user?->id,
            ]
        );

        if ($view->wasRecentlyCreated) {
            $listing->increment('views_count');
            return true;
        }

        return false;
    }
}
