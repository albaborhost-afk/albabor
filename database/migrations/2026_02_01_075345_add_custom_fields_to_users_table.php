<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->after('email');
            $table->enum('account_type', ['user', 'vendor', 'admin'])->default('user')->after('phone');
            $table->boolean('verified_badge')->default(false)->after('account_type');
            $table->enum('verification_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('verified_badge');
            $table->boolean('is_blocked')->default(false)->after('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'account_type', 'verified_badge', 'verification_status', 'is_blocked']);
        });
    }
};
