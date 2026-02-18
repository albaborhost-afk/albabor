<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_picture',
        'avatar',
        'account_type',
        'verified_badge',
        'verification_status',
        'is_blocked',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_picture_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified_badge' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    // Relationships
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedListings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest();
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    public function latestVerificationRequest(): HasOne
    {
        return $this->hasOne(VerificationRequest::class)->latest();
    }

    public function buyerTickets(): HasMany
    {
        return $this->hasMany(MediationTicket::class, 'buyer_id');
    }

    public function sellerTickets(): HasMany
    {
        return $this->hasMany(MediationTicket::class, 'seller_id');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->account_type === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->account_type === 'vendor';
    }

    public function isVerified(): bool
    {
        return $this->verified_badge === true;
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked === true;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function canPublishEngineOrParts(): bool
    {
        return $this->isVendor() && $this->hasActiveSubscription();
    }

    public function hasFavorited(Listing $listing): bool
    {
        return $this->favorites()->where('listing_id', $listing->id)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && !$this->isBlocked();
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        if ($this->avatar) {
            return $this->avatar;
        }

        return null;
    }

    public function getAccountTypeLabelAttribute(): string
    {
        return match($this->account_type) {
            'user' => 'Utilisateur',
            'vendor' => 'Vendeur professionnel',
            'admin' => 'Administrateur',
            default => $this->account_type,
        };
    }

    public function getVerificationStatusLabelAttribute(): string
    {
        return match($this->verification_status) {
            'none' => 'Non soumis',
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Refusé',
            default => $this->verification_status,
        };
    }
}
