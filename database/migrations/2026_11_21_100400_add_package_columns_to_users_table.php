<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Package columns on users.
 *
 * Every column is nullable with no default, and nothing is backfilled: NULL
 * package_expires_at means Permanent, so every existing account stays exactly
 * as it is today. This is the lesson of the 2026_08_03 password-expiry lockout,
 * where a retroactive rule locked people out of accounts they had always used.
 */
return new class extends Migration
{
    public function up(): void
    {
        $previous = Schema::hasColumn('users', 'password_changed_at') ? 'password_changed_at' : null;

        $columns = [
            'package_id' => fn (Blueprint $t) => $t->unsignedBigInteger('package_id')->nullable(),
            'package_started_at' => fn (Blueprint $t) => $t->dateTime('package_started_at')->nullable(),
            'package_expires_at' => fn (Blueprint $t) => $t->dateTime('package_expires_at')->nullable()->index(),
            'package_source' => fn (Blueprint $t) => $t->string('package_source', 16)
                ->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable(),
            'package_reminder_7d_sent_at' => fn (Blueprint $t) => $t->dateTime('package_reminder_7d_sent_at')->nullable(),
            'package_reminder_1d_sent_at' => fn (Blueprint $t) => $t->dateTime('package_reminder_1d_sent_at')->nullable(),
            'package_expired_logged_at' => fn (Blueprint $t) => $t->dateTime('package_expired_logged_at')->nullable(),
        ];

        foreach ($columns as $column => $define) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column, $define, $previous) {
                    $definition = $define($table);

                    if ($previous) {
                        $definition->after($previous);
                    }

                    if ($column === 'package_id') {
                        $table->foreign('package_id')->references('id')->on('packages')->nullOnDelete();
                    }
                });
            }

            $previous = $column;
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'package_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['package_id']);
            });
        }

        if (Schema::hasColumn('users', 'package_expires_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['package_expires_at']);
            });
        }

        foreach ([
            'package_id', 'package_started_at', 'package_expires_at', 'package_source',
            'package_reminder_7d_sent_at', 'package_reminder_1d_sent_at', 'package_expired_logged_at',
        ] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
