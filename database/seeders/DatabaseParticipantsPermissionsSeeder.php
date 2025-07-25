<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseParticipantsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create new database participants permissions
        $permissionGroup = 'database';
        $permissions = [
            'create_database_users' => 'Create Database Users',
            'edit_database_users' => 'Edit Database Users',
            'delete_database_users' => 'Delete Database Users',
            'view_database_users' => 'View Database Users',
        ];

        // Create permissions
        foreach ($permissions as $permission => $display_name) {
            $permissionExists = Permission::where('name', $permission)->first();
            
            if (!$permissionExists) {
                Permission::create([
                    'name' => $permission, 
                    'display_name' => $display_name,
                    'guard_name' => 'web',
                    'group' => $permissionGroup,
                    'description' => 'Permission to ' . strtolower($display_name)
                ]);
            }
        }

        // Assign permissions to Administrator role
        $adminRole = Role::where('name', 'Administrator')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'create_database_users',
                'edit_database_users',
                'delete_database_users',
                'view_database_users',
            ]);
        }

        // Assign permissions to Organizer role
        $organizerRole = Role::where('name', 'Organizer')->first();
        if ($organizerRole) {
            $organizerRole->givePermissionTo([
                'create_database_users',
                'edit_database_users',
                'delete_database_users',
                'view_database_users',
            ]);
        }
    }
} 