<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réglage de confidentialité : publier sous « Invité ».
 *
 * Certains vendeurs ne veulent pas que leur identité apparaisse sur leurs
 * annonces — l'acheteur les contacte par téléphone ou par la messagerie du
 * site. Le nom et la photo de profil sont alors masqués partout où un tiers
 * les verrait ; le vendeur lui-même et l'administration voient le vrai nom.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hide_name')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hide_name');
        });
    }
};
