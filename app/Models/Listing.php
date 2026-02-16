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
        'price_dzd',
        'currency',
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
        'mediation_enabled',
        'numero_whatsapp',
        'numero_mobile',
        'contact_email',
        'views_count',
        'favorites_count',
        'specs',
    ];

    protected function casts(): array
    {
        return [
            'mediation_enabled' => 'boolean',
            'published_until' => 'datetime',
            'featured_until' => 'datetime',
            'specs' => 'array',
            'views_count' => 'integer',
            'favorites_count' => 'integer',
            'price_dzd' => 'integer',
            'currency' => 'string',
        ];
    }

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

    const PROPULSION_OPTIONS = ['Hors-Bord', 'In-bord'];
    const CARBURANT_OPTIONS = ['Essence', 'Diesel', 'Électrique', 'Hybride'];
    const IMMATRICULATION_OPTIONS = ['Algérien', 'Polonais', 'Espagnol', 'Français', 'Italien', 'Autre'];

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
        return $this->media->pluck('path')->toArray();
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency === 'EUR') {
            return number_format($this->price_dzd, 0, ',', ' ') . ' €';
        }

        return number_format($this->price_dzd, 0, ',', ' ') . ' DA';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency === 'EUR' ? '€' : 'DA';
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
