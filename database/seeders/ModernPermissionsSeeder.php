<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class ModernPermissionsSeeder extends Seeder
{
    /**
     * Seed the modern permission scheme.
     *
     * The group order below mirrors the sidebar exactly:
     *   Dashboard
     *   Event Operations  -> Event (Event Management, Survey), Participants, Attendance, Certificate
     *   Engagement        -> PWA Management, Campaign
     *   Insights          -> Reports
     *   System            -> Helpdesk, Settings (Global Config incl. Recycle Bin, Roles, Users, Logs, Audit)
     *
     * Every permission also receives an explicit sort_order so the Role
     * Management matrix renders in this same sequence.
     */
    public function run(): void
    {
        $modernPermissions = [
            // ── Dashboard ────────────────────────────────────────────────
            'dashboard' => [
                ['name' => 'dashboard.read', 'display_name' => 'View Dashboard', 'description' => 'Access dashboard'],
            ],

            // ── Event Operations: Event (Event Management + Survey) ──────
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
                ['name' => 'surveys.publish', 'display_name' => 'Publish Surveys', 'description' => 'Publish/unpublish surveys'],
                ['name' => 'survey_questions.manage', 'display_name' => 'Manage Survey Questions', 'description' => 'Add/edit/delete survey questions'],
                ['name' => 'survey_responses.read', 'display_name' => 'View Survey Responses', 'description' => 'View survey responses'],
                ['name' => 'survey_responses.export', 'display_name' => 'Export Survey Responses', 'description' => 'Export survey data'],
            ],

            // ── Event Operations: Participants ───────────────────────────
            'participants' => [
                ['name' => 'participants.create', 'display_name' => 'Create Participants', 'description' => 'Add new participants'],
                ['name' => 'participants.read', 'display_name' => 'View Participants', 'description' => 'View participant list'],
                ['name' => 'participants.update', 'display_name' => 'Edit Participants', 'description' => 'Edit participant information'],
                ['name' => 'participants.delete', 'display_name' => 'Delete Participants', 'description' => 'Delete participants'],
            ],

            // ── Event Operations: Attendance ─────────────────────────────
            'attendance' => [
                ['name' => 'attendance_management.read', 'display_name' => 'View Attendance Management', 'description' => 'Access attendance management pages'],
                ['name' => 'attendance.create', 'display_name' => 'Create Attendance', 'description' => 'Create attendance sessions'],
                ['name' => 'attendance.read', 'display_name' => 'View Attendance', 'description' => 'View attendance records'],
                ['name' => 'attendance.update', 'display_name' => 'Edit Attendance', 'description' => 'Edit attendance records'],
                ['name' => 'attendance.delete', 'display_name' => 'Delete Attendance', 'description' => 'Delete attendance records'],
                ['name' => 'attendance.archive', 'display_name' => 'Archive/Unarchive Attendance', 'description' => 'Archive or unarchive attendance sessions'],
                ['name' => 'archives.read', 'display_name' => 'View Archives', 'description' => 'View archived attendance'],
                ['name' => 'archives.archive', 'display_name' => 'Unarchive from Archive Page', 'description' => 'Unarchive from archive page'],
                ['name' => 'archives.delete', 'display_name' => 'Delete Archives', 'description' => 'Delete archived attendance sessions'],
            ],

            // ── Event Operations: Certificate ────────────────────────────
            'certificate' => [
                ['name' => 'certificates.read', 'display_name' => 'View Certificates', 'description' => 'View certificate list'],
                ['name' => 'certificates.create', 'display_name' => 'Generate Certificates', 'description' => 'Generate new certificates'],
                ['name' => 'certificates.delete', 'display_name' => 'Delete Certificates', 'description' => 'Delete certificates'],
                ['name' => 'templates.create', 'display_name' => 'Create Templates', 'description' => 'Create certificate templates'],
                ['name' => 'templates.read', 'display_name' => 'View Templates', 'description' => 'View templates'],
                ['name' => 'templates.update', 'display_name' => 'Edit Templates', 'description' => 'Edit certificate templates'],
                ['name' => 'templates.delete', 'display_name' => 'Delete Templates', 'description' => 'Delete templates'],
            ],

            // ── Engagement: PWA Management ───────────────────────────────
            'pwa' => [
                ['name' => 'pwa_participants.create', 'display_name' => 'Create PWA Participants', 'description' => 'Add PWA participants'],
                ['name' => 'pwa_participants.read', 'display_name' => 'View PWA Participants', 'description' => 'View PWA participants'],
                ['name' => 'pwa_participants.update', 'display_name' => 'Edit PWA Participants', 'description' => 'Edit PWA participants'],
                ['name' => 'pwa_participants.delete', 'display_name' => 'Delete PWA Participants', 'description' => 'Delete PWA participants'],
                // The reset and ban buttons existed on the listing with nothing
                // guarding them, so anyone who could see the page could use them.
                ['name' => 'pwa_participants.reset_password', 'display_name' => 'Reset PWA Passwords', 'description' => 'Generate and email a new password for a PWA participant'],
                ['name' => 'pwa_participants.ban', 'display_name' => 'Ban PWA Participants', 'description' => 'Ban a participant from signing in and from registering again'],
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

            // ── Engagement: Campaign ────────────────────────────────────
            'campaign' => [
                ['name' => 'campaigns.create', 'display_name' => 'Create Campaigns', 'description' => 'Create email/SMS campaigns'],
                ['name' => 'campaigns.read', 'display_name' => 'View Campaigns', 'description' => 'View campaigns'],
                ['name' => 'campaigns.update', 'display_name' => 'Edit Campaigns', 'description' => 'Edit campaigns'],
                ['name' => 'campaigns.delete', 'display_name' => 'Delete Campaigns', 'description' => 'Delete campaigns'],
                ['name' => 'delivery.read', 'display_name' => 'View Delivery Config', 'description' => 'View delivery settings'],
                ['name' => 'delivery.update', 'display_name' => 'Manage Delivery', 'description' => 'Configure delivery settings'],
            ],

            // ── Insights: Reports ───────────────────────────────────────
            'reports' => [
                ['name' => 'attendance_reports.read', 'display_name' => 'View Attendance Reports', 'description' => 'View attendance reports'],
                ['name' => 'attendance_reports.export', 'display_name' => 'Export Attendance Reports', 'description' => 'Export attendance data'],
                ['name' => 'attendance_reports.delete', 'display_name' => 'Delete Attendance Reports', 'description' => 'Delete attendance report entries'],
                ['name' => 'event_statistics.read', 'display_name' => 'View Event Statistics', 'description' => 'View event statistics'],
                ['name' => 'event_statistics.export', 'display_name' => 'Export Event Statistics', 'description' => 'Export statistics data'],
                ['name' => 'certificate_reports.read', 'display_name' => 'View Certificate Reports', 'description' => 'View certificate reports'],
                ['name' => 'certificate_reports.export', 'display_name' => 'Export Certificate Reports', 'description' => 'Export certificate data'],
                // The report page has delete and resend buttons. Neither had a
                // permission: the delete control was gated on a name that was never
                // seeded, so it was invisible to every role, and both routes asked
                // only for .read.
                ['name' => 'certificate_reports.delete', 'display_name' => 'Delete Certificates', 'description' => 'Delete a certificate from the report'],
                ['name' => 'certificate_reports.send', 'display_name' => 'Resend Certificates', 'description' => 'Email a certificate to its recipient'],
            ],

            // ── System: Helpdesk ────────────────────────────────────────
            'helpdesk' => [
                ['name' => 'helpdesk.create', 'display_name' => 'Create Helpdesk Ticket', 'description' => 'Create new helpdesk tickets'],
                ['name' => 'helpdesk.read', 'display_name' => 'View Helpdesk', 'description' => 'View helpdesk tickets'],
                ['name' => 'helpdesk.update', 'display_name' => 'Manage Helpdesk', 'description' => 'Reply and manage tickets'],
                ['name' => 'helpdesk.delete', 'display_name' => 'Delete Helpdesk Ticket', 'description' => 'Delete helpdesk tickets'],
            ],

            // ── System: Settings ────────────────────────────────────────
            'settings' => [
                // Global Config
                ['name' => 'global_config.read', 'display_name' => 'View Global Config', 'description' => 'View global configuration'],
                ['name' => 'global_config.update', 'display_name' => 'Manage Global Config', 'description' => 'Edit global configuration'],

                // Recycle Bin (a tab inside Global Config)
                ['name' => 'recycle_bin.read', 'display_name' => 'View Recycle Bin', 'description' => 'See soft-deleted records in the Recycle Bin'],
                ['name' => 'recycle_bin.restore', 'display_name' => 'Restore From Recycle Bin', 'description' => 'Restore soft-deleted records'],
                ['name' => 'recycle_bin.delete', 'display_name' => 'Permanently Delete From Recycle Bin', 'description' => 'Permanently remove records from the database'],

                // Role Management
                ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles'],
                ['name' => 'roles.read', 'display_name' => 'View Roles', 'description' => 'View roles'],
                ['name' => 'roles.update', 'display_name' => 'Edit Roles', 'description' => 'Edit roles'],
                ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete roles'],

                // User Management
                ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new users'],
                ['name' => 'users.read', 'display_name' => 'View Users', 'description' => 'View users'],
                ['name' => 'users.update', 'display_name' => 'Edit Users', 'description' => 'Edit users'],
                ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete users'],

                // Log Activity
                ['name' => 'log_activity.read', 'display_name' => 'View Log Activity', 'description' => 'View activity logs'],
                ['name' => 'log_activity.delete', 'display_name' => 'Delete Log Activity', 'description' => 'Clear activity logs'],
                ['name' => 'log_activity.export', 'display_name' => 'Export Log Activity', 'description' => 'Export activity logs'],

                // Security & Audit
                ['name' => 'security_audit.read', 'display_name' => 'View Security Audit', 'description' => 'View security audit logs'],
                ['name' => 'security_audit.delete', 'display_name' => 'Delete Security Audit', 'description' => 'Clear security audit logs'],
                ['name' => 'security_audit.export', 'display_name' => 'Export Security Audit', 'description' => 'Export security audit logs'],
            ],
        ];

        // Groups are numbered in blocks of 1000 so later inserts inside a group
        // never bleed into the next one.
        $groupIndex = 0;

        foreach ($modernPermissions as $group => $perms) {
            $groupIndex++;
            $position = 0;

            foreach ($perms as $perm) {
                $position++;

                Permission::updateOrCreate(
                    ['name' => $perm['name'], 'guard_name' => 'web'],
                    [
                        'display_name' => $perm['display_name'],
                        'group' => $group,
                        'description' => $perm['description'] ?? '',
                        'sort_order' => ($groupIndex * 1000) + $position,
                    ]
                );
            }
        }

        // Cleanup deprecated/unused permissions
        Permission::whereIn('name', [
            'certificates.update', // no edit action in Manage Certificates module

            // Hyphenated names from the scheme that predates this one. Each has a
            // dotted twin above, and nothing in the codebase checks the hyphenated
            // form. Keeping both is not harmless: the role matrix folds a pair into
            // one row and the checkbox ends up carrying whichever was processed
            // last, which is how the Template Designer permission was granted under
            // a name no route or menu ever reads. Roles were moved onto the modern
            // names by the retire_legacy_hyphenated_permissions migration.
            'template-designer.create', 'template-designer.read',
            'template-designer.update', 'template-designer.delete',
            'attendance-reports.read', 'certificate-reports.read',
            'event-statistics.read', 'global-config.read', 'global-config.update',
            'log-activity.read', 'security-audit.read',
        ])->delete();

        // Any remaining permission that predates this scheme keeps working but is
        // pushed to the end of its group so the matrix still reads top-down.
        Permission::where('sort_order', '<', 1000)
            ->orWhere('sort_order', 9999)
            ->update(['sort_order' => 99999]);

        // Administrator must always keep the Recycle Bin permissions, otherwise
        // deleted records would become unreachable.
        $administrator = Role::where('name', 'Administrator')->where('guard_name', 'web')->first();

        if ($administrator) {
            $administrator->givePermissionTo([
                'recycle_bin.read', 'recycle_bin.restore', 'recycle_bin.delete',
                // Reset and ban are actions the Administrator must always be able
                // to take, since they are the fallback when an organizer is rate
                // limited or a participant needs removing from the system.
                'pwa_participants.reset_password',
                'pwa_participants.ban',
                // Certificate templates. These were held only under the retired
                // hyphenated name, so the Administrator could not open the Template
                // Designer at all. Naming them here keeps that from recurring.
                'templates.read', 'templates.create', 'templates.update', 'templates.delete',
            ]);
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Modern permissions created successfully, ordered to match the sidebar.');
    }
}
