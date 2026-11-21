<?php

namespace App\Support;

use App\Models\Package;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Applies the Package field of User Management to an account.
 *
 * Only an Organizer can hold a package; any other role, Administrator included,
 * is made Permanent whatever was posted.
 *
 * No-op rule: when the posted package and the posted start and expiry dates
 * (calendar days, app timezone) equal what is stored, nothing is written,
 * nothing is logged and the reminder tracking is left alone. Saving a user for
 * an unrelated change (a phone number, a name) therefore never rewrites a
 * purchased expiry, never flips its source to "admin" and never makes the
 * expiry reminders go out a second time.
 */
class PackageAssignment
{
    public const ROLE = 'Organizer';

    /**
     * @return string|null The Activity Log description written, or null when nothing changed.
     */
    public static function apply(User $user, ?Package $package, ?CarbonInterface $start, ?CarbonInterface $end, ?User $admin = null): ?string
    {
        $user->unsetRelation('roles');

        if (! $user->hasRole(self::ROLE)) {
            $package = null;
        }

        $current = [
            'package_id' => $user->package_id,
            'started' => $user->package_started_at?->format('Y-m-d'),
            'expires' => $user->package_expires_at?->format('Y-m-d'),
        ];
        $hadPackage = $user->package_id !== null || $user->package_expires_at !== null;

        if ($package === null || $start === null || $end === null) {
            if (! $hadPackage) {
                return null;
            }

            $oldCode = $user->package?->code;

            static::write($user, [
                'package_id' => null,
                'package_started_at' => null,
                'package_expires_at' => null,
                'package_source' => null,
                'package_reminder_7d_sent_at' => null,
                'package_reminder_1d_sent_at' => null,
                'package_expired_logged_at' => null,
            ]);

            return static::log('Package set to Permanent by administrator', $user, $admin, [
                'old_package' => $oldCode,
                'old_expires_at' => $current['expires'],
            ]);
        }

        $timezone = config('app.timezone');
        $startDay = Carbon::parse($start->format('Y-m-d'), $timezone)->startOfDay();
        $endDay = Carbon::parse($end->format('Y-m-d'), $timezone)->endOfDay();

        $samePackage = (int) $current['package_id'] === (int) $package->id;
        $sameStart = $current['started'] === $startDay->format('Y-m-d');
        $sameExpiry = $current['expires'] === $endDay->format('Y-m-d');

        if ($samePackage && $sameStart && $sameExpiry) {
            return null;
        }

        $previousExpiry = $user->package_expires_at?->copy();
        $oldCode = $user->package?->code;

        $values = [
            'package_id' => $package->id,
            'package_started_at' => $startDay,
            'package_expires_at' => $endDay,
            'package_source' => 'admin',
        ];

        if (! $samePackage || ! $sameExpiry) {
            $values += [
                'package_reminder_7d_sent_at' => null,
                'package_reminder_1d_sent_at' => null,
                'package_expired_logged_at' => null,
            ];
        }

        static::write($user, $values);

        $description = match (true) {
            ! $hadPackage => 'Package assigned by administrator',
            ! $samePackage => 'Package changed by administrator',
            $previousExpiry !== null && $endDay->gt($previousExpiry) => 'Package extended by administrator',
            default => 'Package changed by administrator',
        };

        return static::log($description, $user, $admin, [
            'old_package' => $oldCode,
            'new_package' => $package->code,
            'old_expires_at' => $current['expires'],
            'started_at' => $startDay->format('Y-m-d'),
            'expires_at' => $endDay->format('Y-m-d'),
        ]);
    }

    /**
     * Writes the package columns without the User model's own "User updated"
     * activity entry, which would be empty: none of these columns are in its
     * logged attributes.
     *
     * @param  array<string, mixed>  $values
     */
    private static function write(User $user, array $values): void
    {
        $user->disableLogging();

        try {
            $user->forceFill($values)->save();
        } finally {
            $user->enableLogging();
        }

        $user->unsetRelation('package');
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private static function log(string $description, User $user, ?User $admin, array $properties): string
    {
        PaymentLog::activity($description, ['user_id' => $user->id] + $properties, $user, $admin);

        return $description;
    }
}
