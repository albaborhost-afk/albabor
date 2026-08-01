<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /** Nom affiché à la place du vrai nom quand le compte se masque. */
    public const ANONYMOUS_NAME = 'Invité';

    protected $fillable = [
        'name',
        'hide_name',
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
        // Image encodée en base64 : `profile_picture_url` sert à l'affichage,
        // la donnée brute n'a rien à faire dans une réponse.
        'profile_picture_data',
    ];

    /**
     * Champs retirés des réponses destinées à un tiers.
     *
     * `GET /api/v1/listings` est public et sérialise le vendeur : l'adresse
     * e-mail de chaque vendeur était donc récupérable sans compte, en une
     * requête, avec en prime des indicateurs internes (compte bloqué,
     * publication gratuite, identifiant Google).
     */
    private const VIEWER_RESTRICTED_FIELDS = [
        'email',
        'email_verified_at',
        'google_id',
        'is_blocked',
        'free_publishing',
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
            'hide_name' => 'boolean',
        ];
    }

    /**
     * Laisse passer le vrai nom sur CETTE instance, quel que soit le lecteur.
     *
     * À réserver aux réponses destinées au compte lui-même (connexion,
     * inscription, son propre profil) : sans cela, un utilisateur qui se masque
     * verrait « Invité » à la place de son propre nom dans l'application.
     */
    protected bool $realNameRevealed = false;

    // ── Confidentialité : publier sous « Invité » ──────────────────────────
    //
    // Le masquage est appliqué à la lecture de l'attribut, pas au point
    // d'affichage : toute vue, toute réponse API et tout export passent par là.
    // Le défaut est donc « masqué » — un nouvel écran ne peut pas oublier la
    // règle et divulguer le nom.

    /**
     * Ce lecteur doit-il voir « Invité » à la place du nom ?
     *
     * Ni le compte lui-même ni un administrateur ne sont concernés : le vendeur
     * doit reconnaître son propre profil, et le support a besoin du vrai nom.
     * Hors requête HTTP (tâche planifiée, worker, commande), il n'y a pas de
     * lecteur identifié : on masque, c'est le côté sûr.
     */
    public function identityMasked(): bool
    {
        if ($this->realNameRevealed || ! $this->hidesName()) {
            return false;
        }

        return ! $this->viewerIsSelfOrAdmin();
    }

    /**
     * Qui regarde ? Session pour le site et l'administration, jeton Sanctum
     * pour les applications — y compris sur les routes publiques, où le
     * middleware n'a pas activé le garde et où `auth()` seul renverrait null
     * (le vendeur verrait « Invité » à la place de son propre nom).
     */
    protected static function currentViewer(): ?self
    {
        $viewer = auth()->user();

        if (! $viewer instanceof self) {
            $viewer = auth('sanctum')->user();
        }

        return $viewer instanceof self ? $viewer : null;
    }

    /**
     * Sérialisation : un tiers ne reçoit ni l'e-mail ni les indicateurs
     * internes du compte.
     *
     * Le filtrage se fait ici plutôt qu'à chaque point d'affichage : le
     * défaut devient « masqué », donc un futur écran ne peut pas divulguer
     * l'adresse en oubliant une règle. Le compte lui-même et l'administration
     * gardent tout — c'est le même critère que pour le nom.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if ($this->realNameRevealed || $this->viewerIsSelfOrAdmin()) {
            return $data;
        }

        foreach (self::VIEWER_RESTRICTED_FIELDS as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /** Le lecteur est-il le compte lui-même, ou un administrateur ? */
    private function viewerIsSelfOrAdmin(): bool
    {
        $viewer = static::currentViewer();

        if (! $viewer instanceof self) {
            return false;
        }

        return $viewer->getKey() === $this->getKey() || $viewer->account_type === 'admin';
    }

    /** Le compte a demandé à publier sous « Invité ». */
    public function hidesName(): bool
    {
        return (bool) ($this->attributes['hide_name'] ?? false);
    }

    /**
     * Affiche le vrai nom sur cette instance quel que soit le lecteur.
     * Pour les réponses destinées au compte lui-même (connexion, profil).
     */
    public function withRealName(): static
    {
        $this->realNameRevealed = true;

        return $this;
    }

    /** Nom réel, sans le réglage de confidentialité. Jamais sérialisé. */
    public function getRealNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->identityMasked() ? self::ANONYMOUS_NAME : $value,
        );
    }

    /**
     * L'avatar Google porte le visage du vendeur : le laisser à côté de
     * « Invité » viderait le réglage de son sens.
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->identityMasked() ? null : $value,
        );
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

    /**
     * Règle produit : publier un moteur ou une pièce exige un abonnement
     * vendeur actif (voir CLAUDE.md, section Business).
     *
     * `hasActiveSubscription()` existait mais n'était appelée nulle part : il
     * suffisait donc de passer son compte en « vendeur » — ce qui est gratuit
     * et instantané — pour publier autant de moteurs et de pièces que voulu
     * sans jamais payer. L'interface affichait déjà l'invitation à s'abonner
     * dans ce cas ; seule cette méthode ne la déclenchait jamais.
     */
    public function canPublishEngineOrParts(): bool
    {
        // Dérogation accordée au cas par cas depuis l'administration.
        if ($this->hasFreePublishing()) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->isVendor() && $this->hasActiveSubscription();
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
        // Compte masqué : pas de photo non plus, sinon « Invité » resterait
        // identifiable au premier coup d'œil.
        if ($this->identityMasked()) {
            return null;
        }

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
