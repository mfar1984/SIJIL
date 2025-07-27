<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Survey permissions
        $surveyPermissions = [
            ['name' => 'view_surveys', 'description' => 'View surveys', 'guard_name' => 'web'],
            ['name' => 'create_surveys', 'description' => 'Create surveys', 'guard_name' => 'web'],
            ['name' => 'edit_surveys', 'description' => 'Edit surveys', 'guard_name' => 'web'],
            ['name' => 'delete_surveys', 'description' => 'Delete surveys', 'guard_name' => 'web'],
            ['name' => 'manage_survey_questions', 'description' => 'Manage survey questions', 'guard_name' => 'web'],
            ['name' => 'view_survey_responses', 'description' => 'View survey responses', 'guard_name' => 'web'],
            ['name' => 'export_survey_responses', 'description' => 'Export survey responses', 'guard_name' => 'web'],
            ['name' => 'publish_surveys', 'description' => 'Publish surveys', 'guard_name' => 'web'],
        ];

        // Insert Survey permissions directly using DB query to ensure they're created
        foreach ($surveyPermissions as $permData) {
            DB::table('permissions')->updateOrInsert(
                [
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name']
                ],
                [
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name'],
                    'description' => $permData['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Get IDs for permissions
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($surveyPermissions, 'name'))
            ->pluck('id')
            ->toArray();

        // Assign Survey permissions to Administrator role
        $adminRole = Role::where('name', 'Administrator')->first();
        if ($adminRole) {
            foreach ($permissionIds as $permId) {
                // Check if relationship already exists
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permId)
                    ->where('role_id', $adminRole->id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id' => $adminRole->id
                    ]);
                }
            }
        }

        $this->command->info('Survey permissions created and assigned to Administrator role.');
    }
}
