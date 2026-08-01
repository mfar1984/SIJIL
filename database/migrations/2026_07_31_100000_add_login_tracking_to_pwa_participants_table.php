<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The PwaParticipant model has always listed last_login_at, login_attempts and
 * locked_until as fillable/cast attributes, but the columns were commented out
 * of the original create migration, so they never existed. Any code touching
 * them threw "Unknown column". This adds them for real.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('password_changed_at');
            }

            if (!Schema::hasColumn('pwa_participants', 'login_attempts')) {
                $table->unsignedInteger('login_attempts')->default(0)->after('last_login_at');
            }

            if (!Schema::hasColumn('pwa_participants', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('login_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['last_login_at', 'login_attempts', 'locked_until'],
                fn ($column) => Schema::hasColumn('pwa_participants', $column)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
