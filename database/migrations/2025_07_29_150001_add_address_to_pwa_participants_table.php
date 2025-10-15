<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'address')) {
                $table->string('address')->nullable()->after('organization');
            }
        });
    }
    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_participants', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
}; 