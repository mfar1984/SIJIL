<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the Security tab needed in order to enforce anything.
 *
 * Password expiry has been on the tab since the beginning with nothing to
 * measure against: there was no record of when a backend user last changed their
 * password, so "expires after 90 days" could never be evaluated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
        });

        Schema::table('global_configs', function (Blueprint $table) {
            // Participant app tokens never expired: config/sanctum.php sets no
            // lifetime, so every token ever issued is still valid.
            if (! Schema::hasColumn('global_configs', 'api_token_lifetime_days')) {
                $table->unsignedSmallInteger('api_token_lifetime_days')->default(0)->after('session_timeout');
            }

            // Audit history grew without bound and had no retention control.
            if (! Schema::hasColumn('global_configs', 'log_retention_days')) {
                $table->unsignedSmallInteger('log_retention_days')->default(0)->after('enable_security_alerts');
            }
        });

        // Existing accounts have no recorded change date. Seeding it with the
        // account's creation date is the honest reading: that is the last time
        // the password is known to have been set. Leaving it null would either
        // exempt everyone from expiry or expire everyone at once.
        if (Schema::hasColumn('users', 'password_changed_at')) {
            \Illuminate\Support\Facades\DB::table('users')
                ->whereNull('password_changed_at')
                ->update(['password_changed_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);
        }

        // The settings row is cached for an hour, so without this the new columns
        // read as null until the cache happens to expire - which on a deploy means
        // the Security tab would show blanks and a save could try to write null
        // into a NOT NULL column.
        \App\Models\GlobalConfig::clearCache();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }
        });

        Schema::table('global_configs', function (Blueprint $table) {
            foreach (['api_token_lifetime_days', 'log_retention_days'] as $column) {
                if (Schema::hasColumn('global_configs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
