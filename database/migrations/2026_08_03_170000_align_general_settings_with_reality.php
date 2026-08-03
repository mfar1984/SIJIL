<?php

use App\Models\GlobalConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrects two General tab values that no column would accept.
 *
 * default_event_status held 'published'. events.status is
 * enum('active','pending','completed'), so applying the setting would have been
 * rejected by MySQL - the same failure that broke the gender column on the PWA
 * auto-assign path. The tab offered draft, published and archived, none of which
 * the events table has ever recognised.
 *
 * date_format held 'd-M-Y'. That is a valid format, but the tab is now restricted
 * to a known list so the value cannot be anything that Carbon::format() would
 * turn into unexpected output, and 'd-M-Y' is kept because it is in that list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('global_configs')) {
            return;
        }

        $map = [
            'published' => 'active',
            'draft' => 'pending',
            'archived' => 'completed',
        ];

        foreach ($map as $old => $new) {
            DB::table('global_configs')
                ->where('default_event_status', $old)
                ->update(['default_event_status' => $new]);
        }

        // Anything else that is not a member of the enum.
        DB::table('global_configs')
            ->whereNotIn('default_event_status', ['pending', 'active', 'completed'])
            ->update(['default_event_status' => 'pending']);

        GlobalConfig::clearCache();
    }

    public function down(): void
    {
        // Not reversed: restoring a value the events table rejects would put the
        // setting back into a state where using it raises a database error.
    }
};
