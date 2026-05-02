<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('listings', function (Blueprint $table) {
            $table->timestamp('last_renewed_at')->nullable()->after('featured_until');
        });
    }
    public function down(): void {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('last_renewed_at');
        });
    }
};
