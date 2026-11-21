<?php

namespace App\Console\Commands;

use App\Mail\PackageExpiryReminder;
use App\Models\User;
use App\Services\Payments\PaymentMailer;
use App\Support\PackageAccess;
use App\Support\SystemSettings;
use Illuminate\Console\Command;

/**
 * Emails Organizers 7 days and 1 day before their package expires, once each,
 * and writes the "Package expired" Activity Log entry once per expiry.
 *
 * The *_sent_at columns are set only when the email actually went out, so a
 * failed send is retried on the next daily run. Access itself is never
 * changed here: expiry is enforced at request time from the stored date.
 */
class SendPackageReminders extends Command
{
    protected $signature = 'packages:remind
        {--dry-run : List who would be emailed without sending or writing anything}';

    protected $description = 'Email Organizers whose package expires within 7 days or 1 day';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $now = now();
        $sent = 0;
        $failed = 0;
        $expired = 0;

        $users = User::whereNotNull('package_expires_at')
            ->where('package_expires_at', '<=', $now->copy()->addDays(7))
            ->whereHas('roles', fn ($q) => $q->where('name', 'Organizer'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'Administrator'))
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'active'))
            ->orderBy('package_expires_at')
            ->get();

        foreach ($users as $user) {
            $expires = $user->package_expires_at;
            $secondsLeft = $now->diffInSeconds($expires, false);

            if ($secondsLeft <= 0) {
                if ($user->package_expired_logged_at === null || $user->package_expired_logged_at->lt($expires)) {
                    $this->line(($dry ? '[dry-run] ' : '')."expired: user #{$user->id} on ".$expires->toDateTimeString());
                    $expired++;

                    if (! $dry) {
                        PackageAccess::recordExpiry($user);
                    }
                }

                continue;
            }

            if ($secondsLeft <= 86400) {
                $column = 'package_reminder_1d_sent_at';
                $days = 1;
            } else {
                $column = 'package_reminder_7d_sent_at';
                $days = (int) ceil($secondsLeft / 86400);
            }

            if ($user->{$column} !== null) {
                continue;
            }

            $label = $column === 'package_reminder_1d_sent_at' ? '1-day' : '7-day';
            $this->line(($dry ? '[dry-run] ' : '')."{$label} reminder: user #{$user->id} ({$user->email}), expires ".$expires->toDateTimeString());

            if ($dry) {
                $sent++;

                continue;
            }

            $ok = PaymentMailer::send(
                new PackageExpiryReminder($user, $days, SystemSettings::date($expires)),
                $user->email,
                'package-reminder-'.$label.':'.$user->id
            );

            if ($ok) {
                $user->disableLogging();

                try {
                    $user->forceFill([$column => now()])->save();
                } finally {
                    $user->enableLogging();
                }

                $sent++;
            } else {
                $failed++;
                $this->warn("  not sent to user #{$user->id}; will retry on the next run");
            }
        }

        $this->info(($dry ? '[dry-run] ' : '')."Reminders: {$sent} ".($dry ? 'would be sent' : 'sent').", {$failed} failed, {$expired} expiry entr".($expired === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
    }
}
