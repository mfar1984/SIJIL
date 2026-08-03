<?php

namespace App\Console\Commands;

use App\Support\SecurityPolicy;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

/**
 * Trims the activity log to the retention window on the Security tab.
 *
 * The log had no retention control at all, so it grew for the life of the
 * installation. Retention is also a privacy matter: entries carry email
 * addresses, IP addresses and user agents.
 */
class PurgeAuditLog extends Command
{
    protected $signature = 'audit:purge {--days= : Override the configured retention window} {--dry-run}';

    protected $description = 'Delete audit log entries older than the configured retention window';

    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : SecurityPolicy::logRetentionDays();

        if ($days <= 0) {
            $this->info('Retention is set to keep everything. Nothing to purge.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);

        $query = Activity::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info('Nothing older than ' . $cutoff->toDateString() . '.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn($count . ' entr(ies) older than ' . $cutoff->toDateString() . ' would be deleted.');

            return self::SUCCESS;
        }

        // Deleted in batches so a large backlog does not hold one long
        // transaction open or exhaust memory.
        $deleted = 0;

        do {
            $batch = Activity::where('created_at', '<', $cutoff)->limit(1000)->pluck('id');

            if ($batch->isEmpty()) {
                break;
            }

            $deleted += Activity::whereIn('id', $batch)->delete();
        } while (true);

        $this->info('Deleted ' . $deleted . ' audit entr(ies) older than ' . $days . ' day(s).');

        SecurityPolicy::audit('settings', 'Audit log purged', [
            'deleted' => $deleted,
            'retention_days' => $days,
        ]);

        return self::SUCCESS;
    }
}
