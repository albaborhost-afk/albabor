<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fachrichtung des Vendors : 'boats' (bateaux / yachts / jet-skis) ou 'parts' (pièces / moteurs).
     * Les boutiques existantes étaient toutes "pièces" → default 'parts'.
     * Sur les plans, specialty est nullable : null = plan valable pour toutes les fachrichtungen.
     */
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_profiles', 'specialty')) {
                $table->enum('specialty', ['boats', 'parts'])->default('parts')->after('user_id');
                $table->index('specialty');
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'specialty')) {
                $table->enum('specialty', ['boats', 'parts'])->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn('specialty');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('specialty');
        });
    }
};
