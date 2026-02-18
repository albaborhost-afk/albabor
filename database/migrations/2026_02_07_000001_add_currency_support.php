<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add currency column to listings (skip if already exists)
        if (Schema::hasTable('listings') && !Schema::hasColumn('listings', 'currency')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->enum('currency', ['DZD', 'EUR'])->default('DZD')->after('price_dzd');
            });
        }

        // Create settings table for exchange rate (skip if already exists)
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value');
                $table->timestamps();
            });
        }

        // Seed default exchange rate (skip if already exists)
        if (DB::table('settings')->where('key', 'exchange_rate_eur_dzd')->count() === 0) {
            DB::table('settings')->insert([
                'key' => 'exchange_rate_eur_dzd',
                'value' => '238.00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('listings', 'currency')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }

        Schema::dropIfExists('settings');
    }
};
