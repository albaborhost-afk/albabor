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
        'phone_country_code',
        'password',
        'profile_picture',
        'profile_picture_data',
        'avatar',
        'account_type',
        'verified_badge',
        'verification_status',
        'is_blocked',
        'free_publishing',
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
            'free_publishing' => 'boolean',
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

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function buyerTickets(): HasMany
    {
        return $this->hasMany(MediationTicket::class, 'buyer_id');
    }

    public function sellerTickets(): HasMany
    {
        return $this->hasMany(MediationTicket::class, 'seller_id');
    }

    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function totalUnreadMessagesCount(): int
    {
        return Message::whereIn('conversation_id', function ($query) {
            $query->select('id')
                ->from('conversations')
                ->where('buyer_id', $this->id)
                ->orWhere('seller_id', $this->id);
        })
        ->where('sender_id', '!=', $this->id)
        ->where(function ($query) {
            $query->whereIn('conversation_id', function ($sub) {
                $sub->select('id')
                    ->from('conversations')
                    ->where('buyer_id', $this->id)
                    ->where(function ($q) {
                        $q->whereNull('buyer_last_read_at')
                          ->orWhereColumn('messages.created_at', '>', 'conversations.buyer_last_read_at');
                    });
            })
            ->orWhereIn('conversation_id', function ($sub) {
                $sub->select('id')
                    ->from('conversations')
                    ->where('seller_id', $this->id)
                    ->where(function ($q) {
                        $q->whereNull('seller_last_read_at')
                          ->orWhereColumn('messages.created_at', '>', 'conversations.seller_last_read_at');
                    });
            });
        })
        ->count();
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
        if ($this->hasFreePublishing()) {
            return true;
        }

        if ($this->isVendor()) {
            return true;
        }

        return false;
    }

    public function hasFreePublishing(): bool
    {
        return $this->free_publishing === true;
    }

    public function hasFavorited(Listing $listing): bool
    {
        return $this->favorites()->where('listing_id', $listing->id)->exists();
    }

    public function hasVendorProfile(): bool
    {
        return $this->vendorProfile()->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isBlocked()) {
            return false;
        }

        // Panel vendeur : réservé aux vendeurs professionnels (pièces / moteurs).
        if ($panel->getId() === 'vendeur') {
            return $this->isVendor();
        }

        // Panel admin (par défaut).
        return $this->isAdmin();
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        // If we have any stored picture (DB base64 or S3 path), serve via proxy
        if ($this->profile_picture_data || $this->profile_picture) {
            return route('profile.picture', ['userId' => $this->id]);
        }

        // Google/OAuth avatar
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
