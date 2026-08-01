<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives permissions an explicit display order so the Role Management matrix
 * follows the same sequence as the sidebar instead of insertion order.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('permissions', 'sort_order')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            // Default is deliberately far above any seeded value so legacy
            // permissions always sort to the end of their group.
            $table->unsignedInteger('sort_order')->default(99999)->after('group')->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('permissions', 'sort_order')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
