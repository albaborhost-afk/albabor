<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Supprime chaque nuit les annonces "awaiting_payment" abandonnées (> 7 jours, aucun paiement soumis)
Schedule::command('listings:cleanup-abandoned')->daily();

// Passe chaque nuit les annonces expirées (published_until dépassé) au statut "expired"
// et nettoie les mises en avant terminées.
Schedule::command('listings:expire')->daily();
