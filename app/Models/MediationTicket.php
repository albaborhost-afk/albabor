<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediationTicket extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'status',
        'buyer_message',
        'admin_notes',
        'fee_amount_dzd',
        'payment_status',
        'assigned_admin_id',
        'messages',
    ];

    protected function casts(): array
    {
        return [
            'fee_amount_dzd' => 'integer',
            'messages' => 'array',
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

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new' => 'Nouveau',
            'in_progress' => 'En cours',
            'awaiting_payment' => 'En attente de paiement',
            'closed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid' => 'Non payé',
            'paid' => 'Payé',
            'waived' => 'Exonéré',
            default => $this->payment_status,
        };
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['new', 'in_progress', 'awaiting_payment']);
    }
}
