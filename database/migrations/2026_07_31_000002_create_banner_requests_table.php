<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes d'espace publicitaire.
 *
 * Un annonceur remplit un formulaire public (ce qu'il veut diffuser, son
 * e-mail, son WhatsApp) et l'administration le rappelle. Le suivi se fait par
 * un statut : sans lui, on ne sait plus qui a déjà été contacté.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_requests', function (Blueprint $table) {
            $table->id();

            $table->string('company_name')->nullable();
            $table->string('contact_name');
            $table->string('email');
            $table->string('whatsapp');
            $table->string('whatsapp_country_code', 8)->nullable();
            $table->text('message');
            $table->unsignedBigInteger('budget_dzd')->nullable();

            // Suivi côté administration.
            $table->string('status')->default('new'); // new | contacted | accepted | rejected
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();

            // L'auteur s'il était connecté : évite de redemander qui c'est.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_requests');
    }
};
