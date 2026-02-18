<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('wilaya')->nullable()->change();
            $table->string('pays', 100)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('wilaya')->nullable(false)->change();
            $table->string('pays', 100)->default('Algérie')->change();
        });
    }
};
