<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Demande d'espace publicitaire envoyée depuis le site.
 */
class BannerRequest extends Model
{
    public const STATUS_NEW       = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_NEW       => 'Nouvelle',
        self::STATUS_CONTACTED => 'Contacté',
        self::STATUS_ACCEPTED  => 'Acceptée',
        self::STATUS_REJECTED  => 'Refusée',
    ];

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'whatsapp',
        'whatsapp_country_code',
        'message',
        'budget_dzd',
        'status',
        'admin_notes',
        'contacted_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'budget_dzd'   => 'integer',
            'contacted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** Numéro complet lisible, indicatif compris. */
    public function getFullWhatsappAttribute(): string
    {
        return trim(($this->whatsapp_country_code ?? '') . ' ' . $this->nationalWhatsappDigits());
    }

    /**
     * Lien de discussion WhatsApp — wa.me n'accepte que des chiffres.
     */
    public function getWhatsappUrlAttribute(): string
    {
        $code = preg_replace('/\D+/', '', (string) $this->whatsapp_country_code);

        return 'https://wa.me/' . $code . $this->nationalWhatsappDigits();
    }

    /**
     * Chiffres du numéro, sans le zéro de départ.
     *
     * Le préfixe national (« 0 » en Algérie comme en France) ne se compose
     * pas derrière un indicatif : +213 0670000000 n'est joignable par
     * personne, et wa.me renvoie un numéro invalide.
     */
    private function nationalWhatsappDigits(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp);

        return str_starts_with($digits, '0') ? substr($digits, 1) : $digits;
    }
}
