<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        // Use raw SQL for maximum compatibility (SQLite + MySQL)
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE listings MODIFY wilaya VARCHAR(255) NULL');
            DB::statement('ALTER TABLE listings MODIFY pays VARCHAR(100) NULL DEFAULT NULL');
        } else {
            // SQLite: use Schema builder with change()
            try {
                Schema::table('listings', function (Blueprint $table) {
                    $table->string('wilaya')->nullable()->change();
                    $table->string('pays', 100)->nullable()->default(null)->change();
                });
            } catch (\Throwable $e) {
                // Column might already be nullable
            }
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('wilaya')->nullable(false)->change();
            $table->string('pays', 100)->default('Algérie')->change();
        });
    }
};
