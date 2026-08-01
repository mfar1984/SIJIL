<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Retires the hyphenated permission names that predate the dotted scheme.
 *
 * Why this matters rather than being tidy-up: every route and every sidebar link
 * checks the modern name, but both roles held only `template-designer.read`, so
 * `@can('templates.read')` failed for everybody and the Template Designer link
 * was hidden - including for the Administrator, which has no Gate bypass.
 *
 * The role management matrix hid the problem. It folds a legacy name and its
 * modern twin into one row, and the checkbox carried whichever permission the
 * ordering happened to process last. That was the legacy one, so ticking the box
 * kept granting a permission nothing checks: the box looked ticked and the menu
 * stayed empty.
 *
 * Any role holding a legacy name is given the modern equivalent first, so no
 * access is lost, and then the duplicate is deleted so the collision cannot come
 * back.
 */
return new class extends Migration
{
    /**
     * Legacy name => the modern name that replaced it.
     */
    private array $replacements = [
        'template-designer.create' => 'templates.create',
        'template-designer.read' => 'templates.read',
        'template-designer.update' => 'templates.update',
        'template-designer.delete' => 'templates.delete',
        'attendance-reports.read' => 'attendance_reports.read',
        'certificate-reports.read' => 'certificate_reports.read',
        'event-statistics.read' => 'event_statistics.read',
        'global-config.read' => 'global_config.read',
        'global-config.update' => 'global_config.update',
        'log-activity.read' => 'log_activity.read',
        'security-audit.read' => 'security_audit.read',
    ];

    public function up(): void
    {
        foreach ($this->replacements as $legacyName => $modernName) {
            $legacy = DB::table('permissions')->where('name', $legacyName)->first();

            if (! $legacy) {
                continue;
            }

            $modern = DB::table('permissions')->where('name', $modernName)->first();

            if ($modern) {
                $this->carryOver($legacy->id, $modern->id);
            }

            DB::table('role_has_permissions')->where('permission_id', $legacy->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $legacy->id)->delete();
            DB::table('permissions')->where('id', $legacy->id)->delete();
        }

        // Spatie caches the permission map for an hour. Without this the fix does
        // not show up until the cache expires.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Give everything that held the legacy permission the modern one instead.
     *
     * Covers roles and any permission granted straight to a user, so nobody
     * silently loses access when the duplicate goes.
     */
    private function carryOver(int $legacyId, int $modernId): void
    {
        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $legacyId)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $modernId,
                'role_id' => $roleId,
            ]);
        }

        $models = DB::table('model_has_permissions')
            ->where('permission_id', $legacyId)
            ->get();

        foreach ($models as $model) {
            DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $modernId,
                'model_type' => $model->model_type,
                'model_id' => $model->model_id,
            ]);
        }
    }

    /**
     * Deliberately not reversed.
     *
     * Recreating the legacy rows would restore names that nothing in the codebase
     * checks, and would reintroduce the collision this migration exists to remove.
     * The modern permissions they were merged into stay in place, so no access is
     * lost by leaving this empty.
     */
    public function down(): void
    {
        //
    }
};
