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
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->after('email');
            }
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->enum('account_type', ['user', 'vendor', 'admin'])->default('user')->after('phone');
            }
            if (!Schema::hasColumn('users', 'verified_badge')) {
                $table->boolean('verified_badge')->default(false)->after('account_type');
            }
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('verified_badge');
            }
            if (!Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('verification_status');
            }
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
