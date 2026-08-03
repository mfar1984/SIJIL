<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Somewhere to store the two brand images that had no home.
 *
 * Branding Settings now offers five images. Three of them - the organization
 * logo, the favicon and the login background - already had columns. The sidebar
 * logo and the login logo did not, so without this the form would accept an
 * upload, validate it, and then have nowhere to put the path: the field would
 * look like it worked and quietly forget every file.
 *
 * These hold the public URL of the stored file, matching the three that came
 * before rather than inventing a second convention.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('global_configs', 'sidebar_logo')) {
                $table->string('sidebar_logo')->nullable()->after('org_logo');
            }

            if (!Schema::hasColumn('global_configs', 'login_logo')) {
                $table->string('login_logo')->nullable()->after('login_background');
            }
        });

        // Cached for an hour, so a stale copy would hide the new columns from
        // anything that reads the config before the cache expires.
        \App\Models\GlobalConfig::clearCache();
    }

    public function down(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['sidebar_logo', 'login_logo'],
                fn ($column) => Schema::hasColumn('global_configs', $column)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });

        \App\Models\GlobalConfig::clearCache();
    }
};
