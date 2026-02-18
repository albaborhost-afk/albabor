<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listing_media')) {
            return;
        }

        Schema::create('listing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_media');
    }
};
