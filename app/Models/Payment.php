<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount_dzd',
        'method',
        'proof_path',
        'stripe_session_id',
        'stripe_payment_intent',
        'status',
        'rejection_reason',
        'listing_id',
        'subscription_id',
        'mediation_ticket_id',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_dzd' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function mediationTicket(): BelongsTo
    {
        return $this->belongsTo(MediationTicket::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getProofUrlAttribute(): string
    {
        return Storage::url($this->proof_path);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'publish_listing' => 'Publication d\'annonce',
            'featured_listing' => 'Mise en avant',
            'vendor_subscription' => 'Abonnement vendeur',
            'mediation_fee' => 'Frais de médiation',
            default => $this->type,
        };
    }

    /**
     * Libellé lisible du moyen de paiement.
     *
     * `stripe` et `card` manquaient : l'administration affichait la valeur
     * brute de la colonne pour tout paiement en ligne.
     */
    public function getMethodLabelAttribute(): string
    {
        return self::methodLabels()[$this->method] ?? (string) $this->method;
    }

    /**
     * Tous les moyens acceptés par la contrainte CHECK de `payments.method`.
     * Source unique : l'administration s'en sert pour ses options et filtres,
     * qui dérivaient jusqu'ici de la liste des seules méthodes manuelles.
     *
     * @return array<string, string>
     */
    public static function methodLabels(): array
    {
        return [
            'stripe'        => 'Paiement en ligne (Stripe)',
            'card'          => 'Carte bancaire',
            'baridimob'     => 'BaridiMob',
            'ccp'           => 'CCP',
            'bank_transfer' => 'BEA – Virement bancaire',
            'paypal'        => 'PayPal',
            'redotpay'      => 'RedotPay – SEPA',
        ];
    }

    /**
     * Couleur de badge par moyen, pour l'administration.
     *
     * @return array<string, string>
     */
    public static function methodColors(): array
    {
        return [
            'stripe'        => 'primary',
            'card'          => 'warning',
            'baridimob'     => 'success',
            'ccp'           => 'info',
            'bank_transfer' => 'purple',
            'paypal'        => 'info',
            'redotpay'      => 'warning',
        ];
    }

    /** Un paiement en ligne est confirmé par Stripe, sans justificatif. */
    public function isOnline(): bool
    {
        return $this->method === 'stripe';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Refusé',
            default => $this->status,
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
