<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How long an organizer must wait between PWA password resets.
 *
 * Each reset generates a password and emails it. Without a limit, an organizer
 * could work down the list resetting everybody, which sends a burst of mail from
 * the shared sender and locks people out of accounts they were already using.
 *
 * Administrators are not limited: they are the ones who deal with the fallout.
 *
 * 60 seconds by default. Zero turns the limit off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('global_configs', 'pwa_reset_cooldown_seconds')) {
                $table->unsignedInteger('pwa_reset_cooldown_seconds')
                    ->default(60)
                    ->after('lockout_duration');
            }
        });

        // GlobalConfig::getConfig() caches the model for an hour, so without this
        // the running application keeps a copy that has no idea the column exists
        // and reads it as null.
        \App\Models\GlobalConfig::clearCache();
    }

    public function down(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            if (Schema::hasColumn('global_configs', 'pwa_reset_cooldown_seconds')) {
                $table->dropColumn('pwa_reset_cooldown_seconds');
            }
        });

        \App\Models\GlobalConfig::clearCache();
    }
};
