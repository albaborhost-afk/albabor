<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Affichage personnalise du prix (convention algerienne)
            // Valeurs : null (default = brut DA / EUR), 'milliard', 'million'
            $table->string('price_display_unit', 20)->nullable()->after('currency_label');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('price_display_unit');
        });
    }
};
