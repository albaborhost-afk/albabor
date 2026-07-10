<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Setting;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'type',
        'price_dzd',
        'currency',
        'currency_label',
        'price_display_unit',
        'type_offre',
        'etat',
        'remarque_echange',
        'wilaya',
        'visible_a',
        'pays',
        'status',
        'rejection_reason',
        'published_until',
        'featured_until',
        'last_renewed_at',
        'mediation_enabled',
        'numero_whatsapp',
        'numero_mobile',
        'contact_email',
        'views_count',
        'favorites_count',
        'specs',
        'video_url',
    ];

    protected function casts(): array
    {
        return [
            'mediation_enabled' => 'boolean',
            'published_until' => 'datetime',
            'featured_until' => 'datetime',
            'last_renewed_at' => 'datetime',
            'specs' => 'array',
            'views_count' => 'integer',
            'favorites_count' => 'integer',
            'price_dzd' => 'integer',
            'currency' => 'string',
        ];
    }

    // ---- Media limits ----
    const MAX_IMAGES = 20;
    const MAX_IMAGE_SIZE_KB = 15360; // 15 MB — server resizes to 1200px anyway

    // ---- Etat (Condition) constants ----
    const ETAT_JAMAIS_UTILISE = 'jamais_utilise';
    const ETAT_COMME_NEUF = 'comme_neuf';
    const ETAT_BON_ETAT = 'bon_etat';
    const ETAT_MOYEN = 'etat_moyen';
    const ETAT_A_REVISER = 'a_reviser';

    const ETAT_LABELS = [
        'jamais_utilise' => 'Jamais Utilisé — État Tout Neuf',
        'comme_neuf' => 'Utilisé — État Comme Neuf',
        'bon_etat' => 'Utilisé — Bon État',
        'etat_moyen' => 'Utilisé — État Moyen',
        'a_reviser' => 'Utilisé — État à réviser',
    ];

    const TYPE_OFFRE_LABELS = [
        'negociable' => 'Négociable',
        'offert' => 'Offert',
        'fix' => 'Prix fixe',
    ];

    const REMARQUE_ECHANGE_LABELS = [
        'accepte' => 'Accepte l\'échange',
        'refuse' => 'N\'accepte pas l\'échange',
    ];

    // ---- Boat Types ----
    const BOAT_TYPES = [
        'yacht' => 'Yacht',
        'voilier_catamaran' => 'Voilier / Catamaran',
        'bateau_moteur' => 'Bateaux à moteur',
        'cabine_cruiser' => 'Cabine Cruiser',
        'semi_rigide' => 'Semi-rigides (RIBs / Bateaux pneumatiques)',
        'bateau_peche' => 'Bateaux de pêche',
    ];

    // ---- Jet-ski Types ----
    const JETSKI_TYPES = [
        'jetski_standard' => 'Jet-ski Standard (à selle)',
        'jetski_bras' => 'Jet-ski à bras (Stand-Up)',
        'jet_car' => 'Jet Car (Voiture de mer)',
        'jetski_sport' => 'Jet-ski Sport / Performance',
        'jetski_peche' => 'Jet-ski de Pêche',
    ];

    // ---- Category Types map (extensible for future categories) ----
    const CATEGORY_TYPES = [
        'boat' => self::BOAT_TYPES,
        'jetski' => self::JETSKI_TYPES,
    ];

    const PROPULSION_OPTIONS = ['Hors-Bord', 'In-bord'];
    const CARBURANT_OPTIONS = ['Essence', 'Diesel', 'Électrique', 'Hybride'];
    const IMMATRICULATION_OPTIONS = ['Algérien', 'Tunisien', 'Marocain', 'Français', 'Espagnol', 'Italien', 'Maltais', 'Grec', 'Turc', 'Autre'];

    /** Flag emoji for each immatriculation option (value stays the French label). */
    const IMMATRICULATION_FLAGS = [
        'Algérien' => '🇩🇿',
        'Tunisien' => '🇹🇳',
        'Marocain' => '🇲🇦',
        'Français' => '🇫🇷',
        'Espagnol' => '🇪🇸',
        'Italien' => '🇮🇹',
        'Maltais' => '🇲🇹',
        'Grec' => '🇬🇷',
        'Turc' => '🇹🇷',
        'Polonais' => '🇵🇱', // plus sélectionnable — conservé pour l'affichage des annonces existantes
        'Autre' => '🌍',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ListingMedia::class)->orderBy('order');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(ListingView::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function mediationTickets(): HasMany
    {
        return $this->hasMany(MediationTicket::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFeatured(): bool
    {
        return $this->featured_until && $this->featured_until->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->published_until && $this->published_until->isPast();
    }

    public function canRenew(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->last_renewed_at === null) {
            return true;
        }
        return $this->last_renewed_at->lt(now()->subDays(7));
    }

    public function daysUntilRenewal(): int
    {
        if ($this->last_renewed_at === null || $this->canRenew()) {
            return 0;
        }
        $nextRenewal = $this->last_renewed_at->copy()->addDays(7);
        $hours = now()->diffInHours($nextRenewal, false);
        return max(1, (int) ceil($hours / 24));
    }

    /**
     * Hide seller contact info based on mediation settings.
     *
     * Rules:
     * - Owner or admin: see everything
     * - mediation_enabled ON: hide phone/WhatsApp/email (contact goes through mediation ticket)
     * - mediation_enabled OFF: everyone (including guests) can see contact info
     */
    public function applyContactVisibility(?User $viewer): self
    {
        $isOwner = $viewer && $viewer->id === $this->user_id;
        $isAdmin = $viewer && method_exists($viewer, 'isAdmin') && $viewer->isAdmin();

        if ($isOwner || $isAdmin) {
            return $this;
        }

        if ($this->mediation_enabled) {
            $this->makeHidden(['numero_whatsapp', 'numero_mobile', 'contact_email']);

            if ($this->relationLoaded('user') && $this->user) {
                $this->user->makeHidden(['phone']);
            }
        }

        return $this;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('published_until')
                  ->orWhere('published_until', '>', now());
            });
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('published_until')
              ->orWhere('published_until', '>', now());
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured_until', '>', now());
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByWilaya($query, $wilaya)
    {
        return $query->where('wilaya', $wilaya);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function getPrimaryImageAttribute()
    {
        return $this->media->first();
    }

    public function getImagesAttribute()
    {
        return $this->media->map(fn (ListingMedia $media) => $media->url)->toArray();
    }

    public function getFormattedPriceAttribute(): string
    {
        // Affichage en convention algerienne (centimes) si demande explicitement
        if ($this->currency === 'DZD' && $this->price_display_unit && $this->price_dzd > 0) {
            if ($this->price_display_unit === 'milliard') {
                $val = $this->price_dzd / 10_000_000; // 1 Mrd centimes = 10 000 000 DA
                return $this->formatCentimesValue($val) . ' ' . ($val >= 2 ? 'Milliards' : 'Milliard');
            }
            if ($this->price_display_unit === 'million') {
                $val = $this->price_dzd / 10_000; // 1 Mio centimes = 10 000 DA
                return $this->formatCentimesValue($val) . ' ' . ($val >= 2 ? 'Millions' : 'Million');
            }
        }

        $symbol = $this->currency_symbol;
        return number_format($this->price_dzd, 0, ',', ' ') . ' ' . $symbol;
    }

    private function formatCentimesValue(float $val): string
    {
        $rounded = round($val, 2);
        if ($rounded == floor($rounded)) {
            return number_format($rounded, 0, ',', ' ');
        }
        $str = number_format($rounded, 2, ',', ' ');
        return rtrim(rtrim($str, '0'), ',');
    }

    public function getCurrencySymbolAttribute(): string
    {
        if ($this->currency === 'EUR') return '€';
        if ($this->currency === 'OTHER') return $this->currency_label ?: '?';
        return 'DA';
    }

    public function getConvertedPriceAttribute(): float
    {
        $rate = Setting::getExchangeRate();

        if ($this->currency === 'EUR') {
            return round($this->price_dzd * $rate);
        }

        return $rate > 0 ? round($this->price_dzd / $rate, 2) : 0;
    }

    public function getFormattedConvertedPriceAttribute(): string
    {
        $converted = $this->converted_price;

        if ($this->currency === 'EUR') {
            return '≈ ' . number_format($converted, 0, ',', ' ') . ' DA';
        }

        return '≈ ' . number_format($converted, 2, ',', ' ') . ' €';
    }

    /**
     * Affichage du prix en centimes algériens (convention populaire en Algérie).
     * 1 DA = 100 centimes -> 1 milliard centimes = 10 000 000 DA.
     * Retourne null si non pertinent (devise EUR/Autre, ou prix < 1 000 000 DA).
     */
    public function getCentimesDisplayAttribute(): ?string
    {
        if ($this->currency !== 'DZD' || $this->price_dzd <= 0) {
            return null;
        }

        // Si formatted_price affiche deja en Milliards/Millions, on n'ajoute pas un doublon
        if ($this->price_display_unit) {
            return null;
        }

        $centimes = (int) $this->price_dzd * 100;

        if ($centimes >= 1_000_000_000) {
            $val = $centimes / 1_000_000_000;
            $rounded = round($val, 1);
            $formatted = $rounded == floor($rounded)
                ? number_format($rounded, 0, ',', ' ')
                : number_format($rounded, 1, ',', ' ');
            $unit = $rounded >= 2 ? 'milliards' : 'milliard';
            return $formatted . ' ' . $unit . ' de centimes';
        }

        if ($centimes >= 100_000_000) {
            $val = round($centimes / 1_000_000);
            return number_format($val, 0, ',', ' ') . ' millions de centimes';
        }

        return null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'boat' => 'Bateau',
            'jetski' => 'Jet-ski',
            'engine' => 'Moteur',
            'parts' => 'Pièces détachées',
            default => $this->category,
        };
    }

    public function getTypeLabelAttribute(): ?string
    {
        if (!$this->type) {
            return null;
        }

        $types = self::CATEGORY_TYPES[$this->category] ?? [];
        return $types[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'awaiting_payment' => 'En attente de paiement',
            'pending_review' => 'En attente de validation',
            'active' => 'Active',
            'rejected' => 'Refusée',
            'sold' => 'Vendue',
            'expired' => 'Expirée',
            'paused' => 'Suspendue',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'awaiting_payment' => 'yellow',
            'pending_review' => 'blue',
            'active' => 'green',
            'rejected' => 'red',
            'sold' => 'purple',
            'expired' => 'orange',
            'paused' => 'gray',
            default => 'gray',
        };
    }

    public function getConditionLabelAttribute(): string
    {
        return self::ETAT_LABELS[$this->etat] ?? $this->etat ?? 'Non spécifié';
    }

    public function getEtatLabelAttribute(): string
    {
        return self::ETAT_LABELS[$this->etat] ?? $this->etat ?? 'Non spécifié';
    }

    public function getEtatShortLabelAttribute(): string
    {
        return match($this->etat) {
            'jamais_utilise' => 'Neuf',
            'comme_neuf' => 'Comme Neuf',
            'bon_etat' => 'Bon État',
            'etat_moyen' => 'État Moyen',
            'a_reviser' => 'À réviser',
            default => 'Non spécifié',
        };
    }

    public function getTypeOffreLabelAttribute(): string
    {
        return self::TYPE_OFFRE_LABELS[$this->type_offre] ?? $this->type_offre ?? 'Négociable';
    }

    public function getRemarqueEchangeLabelAttribute(): string
    {
        return self::REMARQUE_ECHANGE_LABELS[$this->remarque_echange] ?? '';
    }

    public function getSpec(string $section, string $key, $default = null)
    {
        return data_get($this->specs, "{$section}.{$key}", $default);
    }

    public function getSpecSection(string $section): array
    {
        return data_get($this->specs, $section, []);
    }

    public function hasSpecSection(string $section): bool
    {
        $data = $this->getSpecSection($section);
        return !empty(array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []));
    }

    public function scopeByEtat($query, $etat)
    {
        return $query->where('etat', $etat);
    }

    public function scopeByTypeOffre($query, $type)
    {
        return $query->where('type_offre', $type);
    }
}
