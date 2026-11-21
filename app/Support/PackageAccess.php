<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;

/**
 * Answers "has this account's package expired" and "has this event's owner's
 * package expired", from the stored expiry at request time.
 *
 * Permanent accounts (no package_expires_at) and Administrators are never
 * expired. Every enforcement point (login, every web request, public
 * registration, certificate generation and sending, check-in, campaigns and
 * event reminders) asks this class, so there is one rule and no cron flips a
 * flag. A renewal moves package_expires_at forward and everything works again
 * on the next request.
 *
 * Certificate verification and the participant app's certificate endpoints
 * never call this class: certificates already issued keep verifying.
 */
class PackageAccess
{
    public const SUSPENDED_MESSAGE = "This event is not available right now because its organiser's package has expired.";

    /**
     * @var array<int, bool>
     */
    private static array $owners = [];

    public static function flush(): void
    {
        static::$owners = [];
    }

    public static function isExpired(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            $expires = $user->package_expires_at;
        } catch (\Throwable) {
            return false;
        }

        if ($expires === null || $expires->gt(now())) {
            return false;
        }

        return ! $user->hasRole('Administrator');
    }

    public static function ownerExpired(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if (! array_key_exists($userId, static::$owners)) {
            $owner = User::withTrashed()->select('id', 'package_expires_at')->find($userId);
            static::$owners[$userId] = static::isExpired($owner);
        }

        return static::$owners[$userId];
    }

    public static function eventSuspended(?Event $event): bool
    {
        return $event !== null && static::ownerExpired($event->user_id ? (int) $event->user_id : null);
    }

    public static function message(User $user): string
    {
        return 'Your e-Certificate package expired on '.SystemSettings::date($user->package_expires_at)
            .'. Renew it at '.config('payments.pricing_url').' to continue.';
    }

    /**
     * Writes the "Package expired" Activity Log entry once per expiry value.
     */
    public static function recordExpiry(User $user): void
    {
        try {
            $expires = $user->package_expires_at;

            if ($expires === null || $expires->gt(now())) {
                return;
            }

            $logged = $user->package_expired_logged_at;

            if ($logged !== null && $logged->gte($expires)) {
                return;
            }

            PaymentLog::activity('Package expired', [
                'user_id' => $user->id,
                'package_id' => $user->package_id,
                'expired_at' => $expires->toDateTimeString(),
            ], $user, false);

            $user->disableLogging();

            try {
                $user->forceFill(['package_expired_logged_at' => now()])->save();
            } finally {
                $user->enableLogging();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Package expiry not recorded', ['user_id' => $user->id, 'error' => get_class($e)]);
        }
    }
}
