<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class BackfillModernPermissionsSeeder extends Seeder
{
    /**
     * Map legacy permissions to modern equivalents and auto-assign to roles
     */
    public function run(): void
    {
        $legacyToModernMap = [
            // Events
            'view_events' => 'events.read',
            'create_events' => 'events.create',
            'edit_events' => 'events.update',
            'delete_events' => 'events.delete',
            
            // Participants
            'view_participants' => 'participants.read',
            'create_participants' => 'participants.create',
            'edit_participants' => 'participants.update',
            'delete_participants' => 'participants.delete',
            
            // Attendance
            'view_attendance' => 'attendance.read',
            'manage_attendance' => 'attendance.update',
            'view_archives' => 'archives.read',
            
            // Certificates
            'view_certificates' => 'certificates.read',
            'generate_certificates' => 'certificates.create',
            'manage_certificates' => 'certificates.update',
            'edit_templates' => 'templates.update',
            
            // Reports
            'view_attendance_reports' => 'attendance_reports.read',
            'view_event_statistics' => 'event_statistics.read',
            'view_certificate_reports' => 'certificate_reports.read',
            'export_reports' => 'attendance_reports.export',
            
            // Campaign
            'view_campaigns' => 'campaigns.read',
            'create_campaigns' => 'campaigns.create',
            'edit_campaigns' => 'campaigns.update',
            'delete_campaigns' => 'campaigns.delete',
            'manage_delivery' => 'delivery.update',
            
            // Helpdesk
            'view_helpdesk' => 'helpdesk.read',
            'manage_helpdesk' => 'helpdesk.update',
            
            // Settings
            'view_settings' => 'log_activity.read',
            'manage_settings' => 'global_config.update',
            
            // Roles
            'view_roles' => 'roles.read',
            'create_roles' => 'roles.create',
            'edit_roles' => 'roles.update',
            'delete_roles' => 'roles.delete',
            
            // Users
            'view_users' => 'users.read',
            'create_users' => 'users.create',
            'edit_users' => 'users.update',
            'delete_users' => 'users.delete',
            
            // Surveys
            'view_surveys' => 'surveys.read',
            'create_surveys' => 'surveys.create',
            'edit_surveys' => 'surveys.update',
            'delete_surveys' => 'surveys.delete',
            'manage_survey_questions' => 'survey_questions.manage',
            'view_survey_responses' => 'survey_responses.read',
            'export_survey_responses' => 'survey_responses.export',
            'publish_surveys' => 'surveys.publish',
            
            // PWA
            'view_ecertificate_participants' => 'pwa_participants.read',
            'create_ecertificate_participants' => 'pwa_participants.create',
            'edit_ecertificate_participants' => 'pwa_participants.update',
            'delete_ecertificate_participants' => 'pwa_participants.delete',
            'view_ecertificate_analytics' => 'pwa_analytics.read',
            'export_ecertificate_analytics' => 'pwa_analytics.export',
            'manage_ecertificate_templates' => 'pwa_templates.update',
            'manage_ecertificate_settings' => 'pwa_settings.update',
            
            // Dashboard
            'view_dashboard' => 'dashboard.read',
        ];

        $roles = Role::with('permissions')->get();
        
        foreach ($roles as $role) {
            $legacyPermissions = $role->permissions->pluck('name')->toArray();
            $modernPermissionsToAdd = [];
            
            foreach ($legacyPermissions as $legacyName) {
                if (isset($legacyToModernMap[$legacyName])) {
                    $modernName = $legacyToModernMap[$legacyName];
                    $modernPerm = Permission::where('name', $modernName)->first();
                    if ($modernPerm && !$role->hasPermissionTo($modernName)) {
                        $modernPermissionsToAdd[] = $modernPerm->id;
                    }
                }
            }
            
            if (!empty($modernPermissionsToAdd)) {
                $role->permissions()->attach($modernPermissionsToAdd);
                $this->command->info("Added " . count($modernPermissionsToAdd) . " modern permissions to role: {$role->name}");
            }
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('Role permissions backfilled successfully!');
    }
}


