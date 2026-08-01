<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the two permissions the certificate report page always needed.
 *
 * The report has a delete button and a resend button. Delete was gated on
 * `certificate_reports.delete`, a name nothing ever created, so Spatie answered
 * false and the button was invisible to every role - while the route behind it
 * asked only for `certificate_reports.read`. Resend had no permission at all.
 *
 * Both are given to the Administrator and to the Organizer, matching the reach
 * those roles already have over their own certificates.
 */
return new class extends Migration
{
    private array $permissions = [
        'certificate_reports.delete' => ['Delete Certificates', 'Delete a certificate from the report', 8008],
        'certificate_reports.send' => ['Resend Certificates', 'Email a certificate to its recipient', 8009],
    ];

    public function up(): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('name', ['Administrator', 'Organizer'])
            ->pluck('id');

        foreach ($this->permissions as $name => [$displayName, $description, $sortOrder]) {
            $id = DB::table('permissions')->where('name', $name)->value('id');

            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'guard_name' => 'web',
                    'display_name' => $displayName,
                    'description' => $description,
                    'group' => 'reports',
                    'sort_order' => $sortOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $id,
                    'role_id' => $roleId,
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
