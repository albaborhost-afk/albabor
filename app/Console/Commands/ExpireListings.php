<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class ExpireListings extends Command
{
    protected $signature = 'listings:expire
                            {--dry-run : Afficher les annonces concernées sans les modifier}';

    protected $description = 'Passe à "expired" les annonces dont la période de publication est terminée (published_until dépassé) et nettoie les mises en avant expirées.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // 1) Annonces actives dont la période de publication est terminée -> "expired".
        //    (Le scope active() les masque déjà côté public ; ici on synchronise le statut
        //    pour que "Mes annonces" et l'admin reflètent la réalité.)
        $expiredQuery = Listing::where('status', 'active')
            ->whereNotNull('published_until')
            ->where('published_until', '<', now());

        // 2) Mises en avant terminées : on nettoie featured_until dépassé.
        $featuredQuery = Listing::whereNotNull('featured_until')
            ->where('featured_until', '<', now());

        $expiredCount  = (clone $expiredQuery)->count();
        $featuredCount = (clone $featuredQuery)->count();

        if ($expiredCount === 0 && $featuredCount === 0) {
            $this->info('Aucune annonce à expirer et aucune mise en avant à nettoyer.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY-RUN] {$expiredCount} annonce(s) passeraient à \"expired\".");
            $this->warn("[DRY-RUN] {$featuredCount} mise(s) en avant expirée(s) seraient nettoyées.");
            return self::SUCCESS;
        }

        $expired  = (clone $expiredQuery)->update(['status' => 'expired']);
        $featured = (clone $featuredQuery)->update(['featured_until' => null]);

        $this->info("Expiration terminée : {$expired} annonce(s) passée(s) à \"expired\", {$featured} mise(s) en avant nettoyée(s).");

        return self::SUCCESS;
    }
}
