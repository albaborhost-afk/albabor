<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'apple_id')) {
                // Le `sub` d'Apple : seul identifiant stable entre deux
                // connexions. L'adresse e-mail n'est transmise QU'À la première
                // autorisation, elle ne peut donc pas servir de clé.
                $table->string('apple_id')->nullable()->unique()->after('google_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('apple_id');
        });
    }
};
