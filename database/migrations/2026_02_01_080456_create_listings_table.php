<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['boat', 'jetski', 'engine', 'parts']);
            $table->unsignedInteger('price_dzd');
            $table->boolean('negotiable')->default(false);
            $table->string('wilaya');
            $table->enum('condition', ['new', 'used'])->default('used');
            $table->enum('status', ['draft', 'awaiting_payment', 'pending_review', 'active', 'rejected', 'sold', 'expired', 'paused'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_until')->nullable();
            $table->timestamp('featured_until')->nullable();
            $table->boolean('mediation_enabled')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('favorites_count')->default(0);
            $table->json('specs')->nullable();
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index(['status', 'wilaya']);
            $table->index(['status', 'featured_until']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
