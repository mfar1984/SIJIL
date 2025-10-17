<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions safely
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Define permission matrix
        $permissionMatrix = [
            'Dashboard' => ['read'],
            'Event' => [
                'Event Management' => ['create', 'read', 'update', 'delete'],
                'Survey' => ['create', 'read', 'update', 'delete'],
            ],
            'Participants' => ['create', 'read', 'update', 'delete'],
            'Certificate' => [
                'All Certificate' => ['read'],
                'Generate Certificate' => ['create', 'read'],
                'Template Designer' => ['create', 'read', 'update', 'delete'],
            ],
            'Attendance' => [
                'Manage Attendance' => ['create', 'read', 'update', 'delete'],
                'Archive' => ['read'],
            ],
            'Reports' => [
                'Attendance Reports' => ['read'],
                'Event Statistics' => ['read'],
                'Certificate Reports' => ['read'],
            ],
            'Campaign' => [
                'Campaign' => ['create', 'read', 'update', 'delete'],
                'Config Delivery' => ['read', 'update'],
            ],
            'Helpdesk' => ['read', 'update'],
            'Settings' => [
                'Global Config' => ['read', 'update'],
                'Role Management' => ['create', 'read', 'update', 'delete'],
                'User Management' => ['create', 'read', 'update', 'delete'],
                'Log Activity' => ['read'],
                'Security & Audit' => ['read'],
            ],
        ];
        
        // Create permissions
        foreach ($permissionMatrix as $main => $sub) {
            if (is_array($sub) && isset($sub[0]) && is_string($sub[0])) {
                // Direct permissions (like Dashboard)
                foreach ($sub as $action) {
                    $permissionName = Str::slug($main) . '.' . $action;
                    Permission::firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ], [
                        'display_name' => ucfirst($action) . ' ' . $main,
                        'group' => Str::slug($main),
                        'description' => 'Permission to ' . $action . ' ' . $main,
                    ]);
                }
            } else {
                // Sub-menu permissions
                foreach ($sub as $subName => $actions) {
                    foreach ($actions as $action) {
                        $permissionName = Str::slug($subName) . '.' . $action;
                        Permission::firstOrCreate([
                            'name' => $permissionName,
                            'guard_name' => 'web',
                        ], [
                            'display_name' => ucfirst($action) . ' ' . $subName,
                            'group' => Str::slug($main),
                            'description' => 'Permission to ' . $action . ' ' . $subName,
                        ]);
                    }
                }
            }
        }
        
        // Add legacy permissions for backward compatibility (excluding database_* which are deprecated)
        $legacyPermissions = [
            'view_roles' => 'View Roles',
            'create_roles' => 'Create Roles', 
            'edit_roles' => 'Edit Roles',
            'delete_roles' => 'Delete Roles',
            'view_users' => 'View Users',
            'create_users' => 'Create Users',
            'edit_users' => 'Edit Users', 
            'delete_users' => 'Delete Users',
            'view_events' => 'View Events',
            'create_events' => 'Create Events',
            'edit_events' => 'Edit Events',
            'delete_events' => 'Delete Events',
            'view_participants' => 'View Participants',
            'create_participants' => 'Create Participants',
            'edit_participants' => 'Edit Participants',
            'delete_participants' => 'Delete Participants',
            'view_certificates' => 'View Certificates',
            'generate_certificates' => 'Generate Certificates',
            'edit_templates' => 'Edit Templates',
            'view_attendance' => 'View Attendance',
            'manage_attendance' => 'Manage Attendance',
            'view_archives' => 'View Archives',
            'view_attendance_reports' => 'View Attendance Reports',
            'view_event_statistics' => 'View Event Statistics',
            'view_certificate_reports' => 'View Certificate Reports',
            'export_reports' => 'Export Reports',
            'view_campaigns' => 'View Campaigns',
            'create_campaigns' => 'Create Campaigns',
            'edit_campaigns' => 'Edit Campaigns',
            'delete_campaigns' => 'Delete Campaigns',
            // 'view_database_users' => 'View Database Users', // deprecated
            'manage_delivery' => 'Manage Delivery',
            'view_helpdesk' => 'View Helpdesk',
            'manage_helpdesk' => 'Manage Helpdesk',
            'view_settings' => 'View Settings',
            'manage_settings' => 'Manage Settings',
            // 'create_database_users' => 'Create Database Users',
            // 'edit_database_users' => 'Edit Database Users',
            // 'delete_database_users' => 'Delete Database Users',
            'view_surveys' => 'View Surveys',
            'create_surveys' => 'Create Surveys',
            'edit_surveys' => 'Edit Surveys',
            'delete_surveys' => 'Delete Surveys',
            'manage_survey_questions' => 'Manage Survey Questions',
            'view_survey_responses' => 'View Survey Responses',
            'export_survey_responses' => 'Export Survey Responses',
            'publish_surveys' => 'Publish Surveys',
        ];
        
        foreach ($legacyPermissions as $name => $displayName) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ], [
                'display_name' => $displayName,
                'group' => 'legacy',
                'description' => 'Legacy permission: ' . $displayName,
            ]);
        }
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('Permission matrix seeded successfully!');
    }
} 