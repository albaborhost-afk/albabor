<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediation_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['new', 'in_progress', 'awaiting_payment', 'closed', 'cancelled'])->default('new');
            $table->text('buyer_message')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('fee_amount_dzd')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status']);
            $table->index('listing_id');
            $table->index('buyer_id');
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediation_tickets');
    }
};
