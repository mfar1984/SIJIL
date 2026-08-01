<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds soft-delete support to every entity that can be deleted from the UI.
 *
 * With soft deletes in place the rows stay in MySQL, so the existing
 * "ON DELETE CASCADE" foreign keys no longer fire on a user-initiated delete.
 * Deleting a participant therefore stops destroying its certificates.
 */
return new class extends Migration
{
    /**
     * Tables that gain a deleted_at column.
     */
    private array $tables = [
        'events',
        'participants',
        'certificates',
        'campaigns',
        'certificate_templates',
        'attendances',
        'helpdesk_tickets',
        'pwa_participants',
        'pwa_email_templates',
        'users',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
