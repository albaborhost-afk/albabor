<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupAbandonedListings extends Command
{
    protected $signature = 'listings:cleanup-abandoned
                            {--days=7 : Supprimer les annonces en attente de paiement plus anciennes que ce nombre de jours}
                            {--dry-run : Afficher les annonces qui seraient supprimées sans les supprimer}';

    protected $description = 'Supprime les annonces "awaiting_payment" abandonnées (aucun paiement soumis) plus anciennes que --days jours';

    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $dryRun  = $this->option('dry-run');
        $disk    = config('filesystems.listing_disk', 'public');

        $abandoned = Listing::where('status', 'awaiting_payment')
            ->where('created_at', '<', now()->subDays($days))
            ->whereDoesntHave('payments')
            ->with('media')
            ->get();

        if ($abandoned->isEmpty()) {
            $this->info("Aucune annonce abandonnée trouvée (> {$days} jours, sans paiement).");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY-RUN] {$abandoned->count()} annonce(s) seraient supprimées :");
            foreach ($abandoned as $listing) {
                $this->line("  - ID {$listing->id} : \"{$listing->title}\" (créée le {$listing->created_at->toDateString()})");
            }
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($abandoned as $listing) {
            foreach ($listing->media as $media) {
                Storage::disk($disk)->delete($media->path);
                if ($media->thumbnail_path) {
                    Storage::disk($disk)->delete($media->thumbnail_path);
                }
            }

            $listing->delete();
            $count++;
        }

        $this->info("Suppression terminée : {$count} annonce(s) abandonnée(s) supprimées (> {$days} jours, sans paiement soumis).");

        return self::SUCCESS;
    }
}
