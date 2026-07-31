<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Fréquentation du site : un visiteur, un jour, n pages vues.
 */
class SiteVisit extends Model
{
    protected $fillable = [
        'visit_date',
        'visitor_hash',
        'user_id',
        'page_views',
    ];

    /**
     * `visit_date` reste une chaîne « Y-m-d ».
     *
     * Avec un cast `date`, Eloquent réécrit la valeur au format datetime
     * (« 2026-07-31 00:00:00 ») à l'enregistrement, si bien que
     * `where('visit_date', '2026-07-31')` ne retrouvait plus rien : le
     * compteur de pages restait bloqué à 1 et les graphiques étaient vides.
     */
    protected function casts(): array
    {
        return [
            'page_views' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre une page vue. Première visite du jour → nouvelle ligne,
     * sinon on incrémente le compteur de pages.
     *
     * Le hachage inclut le user-agent : deux personnes derrière la même IP
     * (partage de connexion, très courant en Algérie) comptent séparément.
     * Il est salé par la clé de l'application et par le jour, donc il n'est
     * pas réversible en adresse IP ni traçable d'un jour à l'autre.
     */
    public static function record(string $ip, ?string $userAgent, ?int $userId): void
    {
        $date = now()->toDateString();
        $hash = hash('sha256', $date . '|' . $ip . '|' . ($userAgent ?? '') . '|' . config('app.key'));

        // upsert atomique : deux requêtes simultanées du même visiteur ne
        // peuvent pas créer deux lignes (l'index unique les fusionne).
        $affected = static::where('visit_date', $date)
            ->where('visitor_hash', $hash)
            ->update([
                'page_views' => DB::raw('page_views + 1'),
                'updated_at' => now(),
            ]);

        if ($affected > 0) {
            return;
        }

        try {
            static::create([
                'visit_date'   => $date,
                'visitor_hash' => $hash,
                'user_id'      => $userId,
                'page_views'   => 1,
            ]);
        } catch (\Throwable) {
            // Course entre deux requêtes : l'autre a gagné, on incrémente la sienne.
            static::where('visit_date', $date)
                ->where('visitor_hash', $hash)
                ->update(['page_views' => DB::raw('page_views + 1')]);
        }
    }

    /** Visiteurs uniques sur une journée. */
    public static function uniqueVisitorsOn(string $date): int
    {
        return static::where('visit_date', $date)->count();
    }

    /** Pages vues sur une journée. */
    public static function pageViewsOn(string $date): int
    {
        return (int) static::where('visit_date', $date)->sum('page_views');
    }
}
