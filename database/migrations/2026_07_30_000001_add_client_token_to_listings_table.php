<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jeton d'idempotence envoyé par le formulaire d'annonce.
 *
 * L'envoi d'une annonce transporte jusqu'à 20 photos : la requête peut expirer
 * côté passerelle alors que le serveur a déjà tout enregistré. Le navigateur
 * croit alors à un échec, affiche une erreur et l'utilisateur renvoie tout —
 * ce qui créait un doublon. Avec ce jeton, un second envoi identique retrouve
 * l'annonce déjà créée au lieu d'en créer une nouvelle, et le client peut
 * demander au serveur si son envoi a abouti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('client_token', 64)->nullable()->after('user_id');
            $table->index(['user_id', 'client_token'], 'listings_user_client_token_index');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_user_client_token_index');
            $table->dropColumn('client_token');
        });
    }
};
