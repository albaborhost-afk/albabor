<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['publish_listing', 'featured_listing', 'vendor_subscription', 'mediation_fee']);
            $table->unsignedInteger('amount_dzd');
            $table->enum('method', ['baridimob', 'ccp', 'bank_transfer']);
            $table->string('proof_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('listing_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('mediation_ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
