<?php

use App\Models\GlobalConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns password expiry off as a one-time upgrade step.
 *
 * The setting has held 90 days since the system was installed and nothing ever
 * enforced it. Now that it does, leaving the stored value alone would expire
 * every password older than 90 days the moment this ships - on this database
 * that is three of four accounts including the administrator, who would be sent
 * to the profile page and unable to reach anything else.
 *
 * A control that was decorative must not change behaviour retroactively just
 * because it started working. Switching it on is now a deliberate decision, and
 * the tab reports how many accounts it would affect before you make it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('global_configs') || ! Schema::hasColumn('global_configs', 'password_expiry')) {
            return;
        }

        DB::table('global_configs')
            ->where('password_expiry', '>', 0)
            ->update(['password_expiry' => 0]);

        GlobalConfig::clearCache();
    }

    public function down(): void
    {
        // Deliberately not reversed. Restoring a value that forces a password
        // change across every account is not something a rollback should do.
    }
};
