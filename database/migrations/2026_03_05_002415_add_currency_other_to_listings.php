<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for enums, so we use a string column instead
        // Change currency from enum to string to support 'OTHER' + custom label
        Schema::table('listings', function (Blueprint $table) {
            // Add currency_label for custom currency name
            if (!Schema::hasColumn('listings', 'currency_label')) {
                $table->string('currency_label', 20)->nullable()->after('currency');
            }
        });

        // For MySQL: alter the enum to include OTHER
        // For SQLite (dev): no-op since SQLite uses TEXT anyway
        try {
            DB::statement("ALTER TABLE listings MODIFY COLUMN currency ENUM('DZD','EUR','OTHER') NOT NULL DEFAULT 'DZD'");
        } catch (\Exception $e) {
            // SQLite — already stores as TEXT, no change needed
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'currency_label')) {
                $table->dropColumn('currency_label');
            }
        });

        try {
            DB::statement("ALTER TABLE listings MODIFY COLUMN currency ENUM('DZD','EUR') NOT NULL DEFAULT 'DZD'");
        } catch (\Exception $e) {
            // SQLite
        }
    }
};
