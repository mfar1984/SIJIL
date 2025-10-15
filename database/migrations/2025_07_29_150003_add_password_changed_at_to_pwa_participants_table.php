<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('is_active');
            }
        });
    }
    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_participants', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }
        });
    }
}; 