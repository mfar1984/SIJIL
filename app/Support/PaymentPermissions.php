<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The Payment module's permissions, and the Administrator lock on the two that
 * guard the CHIP secret key.
 *
 * Three layers enforce the lock: the "administrator" route middleware on every
 * Payment tab route (authoritative), filterRoleIds() in Role Management so the
 * permissions cannot be attached to another role even by a crafted request,
 * and the role matrix, which renders a lock instead of a checkbox.
 */
class PaymentPermissions
{
    public const ADMINISTRATOR = 'Administrator';

    public const LOCKED = [
        'payment_settings.read',
        'payment_settings.update',
    ];

    public const ALL = [
        'payment_settings.read',
        'payment_settings.update',
        'payment_transactions.read',
        'payment_transactions.update',
        'payment_subscriptions.read',
        'payment_packages.read',
        'payment_packages.update',
        'payment_webhook_logs.read',
    ];

    /** Read permissions that make the BILLING sidebar group appear. */
    public const PAGE_READ = [
        'payment_transactions.read',
        'payment_subscriptions.read',
        'payment_packages.read',
        'payment_webhook_logs.read',
    ];

    public static function canViewSettings(?User $user): bool
    {
        return static::isAdministrator($user) && static::has($user, 'payment_settings.read');
    }

    public static function canUpdateSettings(?User $user): bool
    {
        return static::isAdministrator($user) && static::has($user, 'payment_settings.update');
    }

    public static function canSeeBilling(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        foreach (self::PAGE_READ as $permission) {
            if (static::has($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    public static function isAdministrator(?User $user): bool
    {
        return $user !== null && $user->hasRole(self::ADMINISTRATOR);
    }

    /**
     * Ids of the locked permissions.
     *
     * @return list<int>
     */
    public static function lockedIds(): array
    {
        return DB::table('permissions')
            ->whereIn('name', self::LOCKED)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * The permission ids a role may actually be given.
     *
     * Drops anything that is not an existing permission id and the locked ids,
     * then gives the locked ids back to the Administrator role only. The role
     * name must be the one stored in the database, never the posted one.
     *
     * @param  array<mixed>  $postedIds
     * @return list<int>
     */
    public static function filterRoleIds(array $postedIds, string $storedRoleName): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(fn ($id) => is_numeric($id) ? (int) $id : 0, $postedIds),
            fn ($id) => $id > 0
        )));

        $existing = $ids === []
            ? []
            : DB::table('permissions')->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $locked = static::lockedIds();
        $allowed = array_values(array_diff($existing, $locked));

        if ($storedRoleName === self::ADMINISTRATOR) {
            $allowed = array_values(array_unique(array_merge($allowed, $locked)));
        }

        sort($allowed);

        return $allowed;
    }

    private static function has(User $user, string $permission): bool
    {
        try {
            return $user->can($permission);
        } catch (\Throwable) {
            // A missing permission row (migration not run yet) makes Spatie throw.
            return false;
        }
    }
}
