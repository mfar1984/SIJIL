<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The Payment module in Role Management.
 *
 * Production runs migrate --force and never the seeders, so the permission rows
 * and the Administrator grants have to arrive here. Granted to the Administrator
 * only. The two payment_settings permissions guard the CHIP secret key and are
 * hard-locked to the Administrator role (App\Support\PaymentPermissions and the
 * "administrator" route middleware); the others may be granted to other roles
 * later from Role Management.
 *
 * Re-runnable: rows are looked up by name before inserting and grants use
 * insertOrIgnore on the pivot's primary key.
 */
return new class extends Migration
{
    private array $permissions = [
        'payment_settings.read' => ['View Payment Settings', 'View the CHIP Collect settings (Administrator only)', 11001],
        'payment_settings.update' => ['Manage Payment Settings', 'Change the CHIP Collect settings (Administrator only)', 11002],
        'payment_transactions.read' => ['View Transactions', 'View payment orders', 11003],
        'payment_transactions.update' => ['Re-check Transactions', 'Re-check an order with CHIP and resend set-password links', 11004],
        'payment_subscriptions.read' => ['View Subscriptions', 'View accounts that hold a package', 11005],
        'payment_packages.read' => ['View Packages', 'View packages and prices', 11006],
        'payment_packages.update' => ['Edit Packages', 'Change package names, prices and availability', 11007],
        'payment_webhook_logs.read' => ['View Webhook Logs', 'View inbound CHIP deliveries', 11008],
    ];

    public function up(): void
    {
        $administratorId = DB::table('roles')
            ->where('name', 'Administrator')
            ->where('guard_name', 'web')
            ->value('id');

        foreach ($this->permissions as $name => [$displayName, $description, $sortOrder]) {
            $id = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->value('id');

            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'guard_name' => 'web',
                    'display_name' => $displayName,
                    'description' => $description,
                    'group' => 'payment',
                    'sort_order' => $sortOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($administratorId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $id,
                    'role_id' => $administratorId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys($this->permissions))->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
