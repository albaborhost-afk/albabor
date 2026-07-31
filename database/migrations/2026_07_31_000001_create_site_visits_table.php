<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fréquentation du site : une ligne par visiteur et par jour.
 *
 * L'administration n'avait aucun chiffre de trafic — seulement les vues par
 * annonce. Agréger par (jour, visiteur) plutôt que journaliser chaque requête
 * garde la table petite : ~1 ligne par visiteur par jour au lieu d'une par
 * page vue, tout en donnant les deux chiffres (visiteurs uniques ET pages vues).
 *
 * Aucune IP n'est stockée en clair : seulement un hachage, comme pour les vues
 * d'annonces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->string('visitor_hash', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('page_views')->default(1);
            $table->timestamps();

            // Un visiteur ne compte qu'une fois par jour ; page_views s'incrémente.
            $table->unique(['visit_date', 'visitor_hash'], 'site_visits_day_visitor_unique');
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
