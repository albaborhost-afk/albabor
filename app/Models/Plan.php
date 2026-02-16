<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_dzd',
        'duration_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_dzd' => 'integer',
            'duration_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_dzd, 0, ',', ' ') . ' DA';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
