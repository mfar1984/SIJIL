<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions grouped by module
        $permissionGroups = [
            'dashboard' => [
                'view_dashboard' => 'View Dashboard',
            ],
            'user_management' => [
                'view_users' => 'View Users',
                'create_users' => 'Create Users',
                'edit_users' => 'Edit Users',
                'delete_users' => 'Delete Users',
            ],
            'role_management' => [
                'view_roles' => 'View Roles',
                'create_roles' => 'Create Roles',
                'edit_roles' => 'Edit Roles',
                'delete_roles' => 'Delete Roles',
            ],
            'event_management' => [
                'view_events' => 'View Events',
                'create_events' => 'Create Events',
                'edit_events' => 'Edit Events',
                'delete_events' => 'Delete Events',
            ],
            'participants' => [
                'view_participants' => 'View Participants',
                'create_participants' => 'Create Participants',
                'edit_participants' => 'Edit Participants',
                'delete_participants' => 'Delete Participants',
            ],
            'certificate' => [
                'view_certificates' => 'View Certificates',
                'generate_certificates' => 'Generate Certificates',
                'edit_templates' => 'Edit Templates',
            ],
            'attendance' => [
                'view_attendance' => 'View Attendance',
                'manage_attendance' => 'Manage Attendance',
                'view_archives' => 'View Archives',
            ],
            'reports' => [
                'view_attendance_reports' => 'View Attendance Reports',
                'view_event_statistics' => 'View Event Statistics',
                'view_certificate_reports' => 'View Certificate Reports',
                'export_reports' => 'Export Reports',
            ],
            'campaign' => [
                'view_campaigns' => 'View Campaigns',
                'create_campaigns' => 'Create Campaigns',
                'edit_campaigns' => 'Edit Campaigns',
                'delete_campaigns' => 'Delete Campaigns',
                'view_database_users' => 'View Database Users',
                'manage_delivery' => 'Manage Delivery',
            ],
            'helpdesk' => [
                'view_helpdesk' => 'View Helpdesk',
                'manage_helpdesk' => 'Manage Helpdesk',
            ],
            'settings' => [
                'view_settings' => 'View Settings',
                'manage_settings' => 'Manage Settings',
            ],
        ];

        // Create all permissions
        $createdPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission => $display_name) {
                $permissionExists = Permission::where('name', $permission)->first();
                
                if (!$permissionExists) {
                    $createdPermissions[] = Permission::create([
                        'name' => $permission, 
                        'display_name' => $display_name,
                        'guard_name' => 'web',
                        'group' => $group,
                        'description' => 'Permission to ' . strtolower($display_name)
                    ]);
                } else {
                    // Update existing permission with new data
                    $permissionExists->update([
                        'display_name' => $display_name,
                        'group' => $group,
                        'description' => 'Permission to ' . strtolower($display_name)
                    ]);
                    $createdPermissions[] = $permissionExists;
                }
            }
        }

        // Create Administrator role and give it all permissions
        $adminRole = Role::where('name', 'Administrator')->first();
        if (!$adminRole) {
            $adminRole = Role::create([
                'name' => 'Administrator',
                'description' => 'System Administrator with full access to all features',
                'guard_name' => 'web',
                'status' => 'active',
                'created_by' => 'System',
            ]);
        }
        
        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Create Organizer role with specific permissions
        $organizerRole = Role::where('name', 'Organizer')->first();
        if (!$organizerRole) {
            $organizerRole = Role::create([
                'name' => 'Organizer',
                'description' => 'Event Organizer with limited permissions',
                'guard_name' => 'web',
                'status' => 'active',
                'created_by' => 'System',
            ]);
        }

        // Organizer permissions
        $organizerPermissions = [
            'view_dashboard',
            
            'view_events',
            'create_events',
            'edit_events',
            'delete_events',
            
            'view_participants',
            'create_participants',
            'edit_participants',
            'delete_participants',
            
            'view_certificates',
            'generate_certificates',
            
            'view_attendance',
            'manage_attendance',
            'view_archives',
            
            'view_attendance_reports',
            'view_event_statistics',
            'view_certificate_reports',
            'export_reports',
            
            'view_campaigns',
            'create_campaigns',
            'edit_campaigns',
            'delete_campaigns',
            'view_database_users',
            'manage_delivery',
            
            'view_helpdesk',
            'manage_helpdesk',
        ];
        
        $organizerRole->syncPermissions($organizerPermissions);

        // Create an admin user
        $adminUser = User::where('email', 'admin@e-certificate.com.my')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Administrator',
                'email' => 'admin@e-certificate.com.my',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'last_login_at' => now(),
            ]);
        }
        
        $adminUser->assignRole('Administrator');
        
        // Create an organizer user
        $organizerUser = User::where('email', 'organizer@e-certificate.com.my')->first();
        if (!$organizerUser) {
            $organizerUser = User::create([
                'name' => 'Organizer',
                'email' => 'organizer@e-certificate.com.my',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }
        
        $organizerUser->assignRole('Organizer');
    }
}
