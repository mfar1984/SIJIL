<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class ModernPermissionsSeeder extends Seeder
{
    /**
     * Seed modern permission scheme aligned with sidebar structure
     */
    public function run(): void
    {
        $modernPermissions = [
            // Dashboard
            'dashboard' => [
                ['name' => 'dashboard.read', 'display_name' => 'View Dashboard', 'description' => 'Access dashboard'],
            ],
            
            // Event (includes Event Management + Survey)
            'event' => [
                // Event Management
                ['name' => 'events.create', 'display_name' => 'Create Events', 'description' => 'Create new events'],
                ['name' => 'events.read', 'display_name' => 'View Events', 'description' => 'View event details'],
                ['name' => 'events.update', 'display_name' => 'Edit Events', 'description' => 'Edit event information'],
                ['name' => 'events.delete', 'display_name' => 'Delete Events', 'description' => 'Delete events'],
                
                // Survey (submodule under Event)
                ['name' => 'surveys.create', 'display_name' => 'Create Surveys', 'description' => 'Create new surveys'],
                ['name' => 'surveys.read', 'display_name' => 'View Surveys', 'description' => 'View surveys'],
                ['name' => 'surveys.update', 'display_name' => 'Edit Surveys', 'description' => 'Edit surveys'],
                ['name' => 'surveys.delete', 'display_name' => 'Delete Surveys', 'description' => 'Delete surveys'],
                ['name' => 'survey_questions.manage', 'display_name' => 'Manage Survey Questions', 'description' => 'Add/edit/delete survey questions'],
                ['name' => 'survey_responses.read', 'display_name' => 'View Survey Responses', 'description' => 'View survey responses'],
                ['name' => 'survey_responses.export', 'display_name' => 'Export Survey Responses', 'description' => 'Export survey data'],
                ['name' => 'surveys.publish', 'display_name' => 'Publish Surveys', 'description' => 'Publish/unpublish surveys'],
            ],
            
            // Participants
            'participants' => [
                ['name' => 'participants.create', 'display_name' => 'Create Participants', 'description' => 'Add new participants'],
                ['name' => 'participants.read', 'display_name' => 'View Participants', 'description' => 'View participant list'],
                ['name' => 'participants.update', 'display_name' => 'Edit Participants', 'description' => 'Edit participant information'],
                ['name' => 'participants.delete', 'display_name' => 'Delete Participants', 'description' => 'Delete participants'],
            ],
            
            // Attendance
            'attendance' => [
                ['name' => 'attendance_management.read', 'display_name' => 'View Attendance Management', 'description' => 'Access attendance management pages'],
                ['name' => 'attendance.create', 'display_name' => 'Create Attendance', 'description' => 'Create attendance sessions'],
                ['name' => 'attendance.read', 'display_name' => 'View Attendance', 'description' => 'View attendance records'],
                ['name' => 'attendance.update', 'display_name' => 'Edit Attendance', 'description' => 'Edit attendance records'],
                ['name' => 'attendance.delete', 'display_name' => 'Delete Attendance', 'description' => 'Delete attendance records'],
                ['name' => 'archives.read', 'display_name' => 'View Archives', 'description' => 'View archived attendance'],
                ['name' => 'attendance.archive', 'display_name' => 'Archive/Unarchive Attendance', 'description' => 'Archive or unarchive attendance sessions'],
                ['name' => 'archives.archive', 'display_name' => 'Unarchive from Archive Page', 'description' => 'Unarchive from archive page'],
                ['name' => 'archives.delete', 'display_name' => 'Delete Archives', 'description' => 'Delete archived attendance sessions'],
            ],
            
            // Certificate
            'certificate' => [
                ['name' => 'certificates.read', 'display_name' => 'View Certificates', 'description' => 'View certificate list'],
                ['name' => 'certificates.create', 'display_name' => 'Generate Certificates', 'description' => 'Generate new certificates'],
                ['name' => 'certificates.delete', 'display_name' => 'Delete Certificates', 'description' => 'Delete certificates'],
                ['name' => 'templates.create', 'display_name' => 'Create Templates', 'description' => 'Create certificate templates'],
                ['name' => 'templates.read', 'display_name' => 'View Templates', 'description' => 'View templates'],
                ['name' => 'templates.update', 'display_name' => 'Edit Templates', 'description' => 'Edit certificate templates'],
                ['name' => 'templates.delete', 'display_name' => 'Delete Templates', 'description' => 'Delete templates'],
            ],
            
            // PWA Management (E-Certificate Online)
            'pwa' => [
                ['name' => 'pwa_participants.create', 'display_name' => 'Create PWA Participants', 'description' => 'Add PWA participants'],
                ['name' => 'pwa_participants.read', 'display_name' => 'View PWA Participants', 'description' => 'View PWA participants'],
                ['name' => 'pwa_participants.update', 'display_name' => 'Edit PWA Participants', 'description' => 'Edit PWA participants'],
                ['name' => 'pwa_participants.delete', 'display_name' => 'Delete PWA Participants', 'description' => 'Delete PWA participants'],
                ['name' => 'pwa_analytics.read', 'display_name' => 'View PWA Analytics', 'description' => 'View PWA analytics'],
                ['name' => 'pwa_analytics.export', 'display_name' => 'Export PWA Analytics', 'description' => 'Export PWA analytics data'],
                ['name' => 'pwa_templates.create', 'display_name' => 'Create Email Templates', 'description' => 'Create PWA email templates'],
                ['name' => 'pwa_templates.read', 'display_name' => 'View Email Templates', 'description' => 'View email templates'],
                ['name' => 'pwa_templates.update', 'display_name' => 'Edit Email Templates', 'description' => 'Edit email templates'],
                ['name' => 'pwa_templates.delete', 'display_name' => 'Delete Email Templates', 'description' => 'Delete email templates'],
                ['name' => 'pwa_templates.export', 'display_name' => 'Export Email Templates', 'description' => 'Export email templates as CSV'],
                ['name' => 'pwa_settings.read', 'display_name' => 'View PWA Settings', 'description' => 'View PWA settings'],
                ['name' => 'pwa_settings.update', 'display_name' => 'Manage PWA Settings', 'description' => 'Configure PWA settings'],
            ],
            
            // Reports
            'reports' => [
                ['name' => 'attendance_reports.read', 'display_name' => 'View Attendance Reports', 'description' => 'View attendance reports'],
                ['name' => 'attendance_reports.export', 'display_name' => 'Export Attendance Reports', 'description' => 'Export attendance data'],
                ['name' => 'attendance_reports.delete', 'display_name' => 'Delete Attendance Reports', 'description' => 'Delete attendance report entries'],
                ['name' => 'event_statistics.read', 'display_name' => 'View Event Statistics', 'description' => 'View event statistics'],
                ['name' => 'event_statistics.export', 'display_name' => 'Export Event Statistics', 'description' => 'Export statistics data'],
                ['name' => 'certificate_reports.read', 'display_name' => 'View Certificate Reports', 'description' => 'View certificate reports'],
                ['name' => 'certificate_reports.export', 'display_name' => 'Export Certificate Reports', 'description' => 'Export certificate data'],
            ],
            
            // Campaign
            'campaign' => [
                ['name' => 'campaigns.create', 'display_name' => 'Create Campaigns', 'description' => 'Create email/SMS campaigns'],
                ['name' => 'campaigns.read', 'display_name' => 'View Campaigns', 'description' => 'View campaigns'],
                ['name' => 'campaigns.update', 'display_name' => 'Edit Campaigns', 'description' => 'Edit campaigns'],
                ['name' => 'campaigns.delete', 'display_name' => 'Delete Campaigns', 'description' => 'Delete campaigns'],
                ['name' => 'delivery.read', 'display_name' => 'View Delivery Config', 'description' => 'View delivery settings'],
                ['name' => 'delivery.update', 'display_name' => 'Manage Delivery', 'description' => 'Configure delivery settings'],
            ],
            
            // Helpdesk
            'helpdesk' => [
                ['name' => 'helpdesk.create', 'display_name' => 'Create Helpdesk Ticket', 'description' => 'Create new helpdesk tickets'],
                ['name' => 'helpdesk.read', 'display_name' => 'View Helpdesk', 'description' => 'View helpdesk tickets'],
                ['name' => 'helpdesk.update', 'display_name' => 'Manage Helpdesk', 'description' => 'Reply and manage tickets'],
                ['name' => 'helpdesk.delete', 'display_name' => 'Delete Helpdesk Ticket', 'description' => 'Delete helpdesk tickets'],
            ],
            
            // Settings
            'settings' => [
                ['name' => 'global_config.read', 'display_name' => 'View Global Config', 'description' => 'View global configuration'],
                ['name' => 'global_config.update', 'display_name' => 'Manage Global Config', 'description' => 'Edit global configuration'],
                ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles'],
                ['name' => 'roles.read', 'display_name' => 'View Roles', 'description' => 'View roles'],
                ['name' => 'roles.update', 'display_name' => 'Edit Roles', 'description' => 'Edit roles'],
                ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete roles'],
                ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new users'],
                ['name' => 'users.read', 'display_name' => 'View Users', 'description' => 'View users'],
                ['name' => 'users.update', 'display_name' => 'Edit Users', 'description' => 'Edit users'],
                ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete users'],
                ['name' => 'log_activity.read', 'display_name' => 'View Log Activity', 'description' => 'View activity logs'],
                ['name' => 'log_activity.delete', 'display_name' => 'Delete Log Activity', 'description' => 'Clear activity logs'],
                ['name' => 'log_activity.export', 'display_name' => 'Export Log Activity', 'description' => 'Export activity logs'],
                ['name' => 'security_audit.read', 'display_name' => 'View Security Audit', 'description' => 'View security audit logs'],
                ['name' => 'security_audit.delete', 'display_name' => 'Delete Security Audit', 'description' => 'Clear security audit logs'],
                ['name' => 'security_audit.export', 'display_name' => 'Export Security Audit', 'description' => 'Export security audit logs'],
            ],
        ];

        foreach ($modernPermissions as $group => $perms) {
            foreach ($perms as $perm) {
                Permission::updateOrCreate(
                    ['name' => $perm['name'], 'guard_name' => 'web'],
                    [
                        'display_name' => $perm['display_name'],
                        'group' => $group,
                        'description' => $perm['description'] ?? '',
                    ]
                );
            }
        }

        // Cleanup deprecated/unused permissions
        Permission::whereIn('name', [
            'certificates.update', // no edit action in Manage Certificates module
        ])->delete();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('Modern permissions created successfully!');
    }
}


