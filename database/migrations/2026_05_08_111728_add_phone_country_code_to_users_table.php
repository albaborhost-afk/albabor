<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // International dial code, e.g. '+213', '+33'. Nullable so the
            // column applies safely to existing rows; we backfill below.
            $table->string('phone_country_code', 6)->nullable()->after('phone');
        });

        // The platform was Algeria-only until now, so every existing user
        // with a phone number is Algerian. Backfill the prefix.
        DB::table('users')
            ->whereNotNull('phone')
            ->whereNull('phone_country_code')
            ->update(['phone_country_code' => '+213']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });
    }
};
