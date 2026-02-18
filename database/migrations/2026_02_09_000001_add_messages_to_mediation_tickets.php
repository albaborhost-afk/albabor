<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mediation_tickets', 'messages')) {
            Schema::table('mediation_tickets', function (Blueprint $table) {
                $table->json('messages')->nullable()->after('buyer_message');
            });
        }
    }

    public function down(): void
    {
        Schema::table('mediation_tickets', function (Blueprint $table) {
            $table->dropColumn('messages');
        });
    }
};
