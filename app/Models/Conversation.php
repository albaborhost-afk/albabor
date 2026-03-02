<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'buyer_last_read_at',
        'seller_last_read_at',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'buyer_last_read_at' => 'datetime',
            'seller_last_read_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id);
    }

    public function isParticipant(User $user): bool
    {
        return $this->buyer_id === $user->id || $this->seller_id === $user->id;
    }

    public function isBuyer(User $user): bool
    {
        return $this->buyer_id === $user->id;
    }

    public function isSeller(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    public function getOtherParticipant(User $user): ?User
    {
        if ($this->buyer_id === $user->id) {
            return $this->seller;
        }

        if ($this->seller_id === $user->id) {
            return $this->buyer;
        }

        return null;
    }

    public function unreadCountFor(User $user): int
    {
        $lastReadAt = $this->isBuyer($user)
            ? $this->buyer_last_read_at
            : ($this->isSeller($user) ? $this->seller_last_read_at : null);

        $query = $this->messages()
            ->where('sender_id', '!=', $user->id);

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }

    public function markAsReadFor(User $user): void
    {
        if ($this->isBuyer($user)) {
            $this->update(['buyer_last_read_at' => now()]);
        } elseif ($this->isSeller($user)) {
            $this->update(['seller_last_read_at' => now()]);
        }
    }
}
