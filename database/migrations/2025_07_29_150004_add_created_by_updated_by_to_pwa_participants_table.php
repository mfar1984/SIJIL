<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('password_changed_at');
            }
            if (!Schema::hasColumn('pwa_participants', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }
    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_participants', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('pwa_participants', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
}; 