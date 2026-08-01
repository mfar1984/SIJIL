<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banning a participant.
 *
 * The status column already has a 'banned' value, but nothing recorded who did it,
 * when, or why - so a ban could not be explained or appealed, and the public
 * registration form had no way to recognise a banned person trying again under the
 * same IC or email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('pwa_participants', 'banned_by')) {
                $table->unsignedBigInteger('banned_by')->nullable()->after('banned_at');
            }

            if (!Schema::hasColumn('pwa_participants', 'ban_reason')) {
                $table->string('ban_reason', 500)->nullable()->after('banned_by');
            }
        });

        // Indexed because the public registration form checks both on every
        // submission, across the whole table.
        Schema::table('pwa_participants', function (Blueprint $table) {
            $indexes = collect(
                \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM pwa_participants")
            )->pluck('Key_name')->unique();

            if (!$indexes->contains('pwa_participants_banned_at_index')) {
                $table->index('banned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_participants', 'banned_at')) {
                $table->dropIndex(['banned_at']);
            }

            $drop = array_values(array_filter(
                ['banned_at', 'banned_by', 'ban_reason'],
                fn ($column) => Schema::hasColumn('pwa_participants', $column)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
