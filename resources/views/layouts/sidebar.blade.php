<x-sidebar>
    {{-- Dashboard --}}
    <div class="category-header relative">
        <a href="{{ route('dashboard') }}" class="block relative">
            <div class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <div class="flex items-center">
                    <span class="material-icons-outlined sidebar-icon">dashboard</span>
                    <p class="sidebar-label">Dashboard</p>
                </div>
            </div>
        </a>
    </div>

    @if(auth()->user()->can('events.read') || auth()->user()->can('participants.read') || auth()->user()->can('attendance_management.read') || auth()->user()->can('attendance.read') || auth()->user()->can('archives.read') || auth()->user()->can('certificates.read') || auth()->user()->can('certificates.create') || auth()->user()->can('templates.read'))
    <p class="sidebar-group-label">Event Operations</p>
    @endif

    @can('events.read')
    <div class="category-header relative" onclick="toggleSection('event-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">event</span>
                <p class="sidebar-label">Event</p>
            </div>
            <svg class="sidebar-chevron" id="event-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="event-section" class="hierarchical-menu" style="display: none;">
        <x-sidebar-submenu-item href="{{ route('event.management') }}" icon="calendar_month" :active="request()->routeIs('event.management')">
            Event Management
        </x-sidebar-submenu-item>
        @can('surveys.read')
        <x-sidebar-submenu-item href="{{ route('survey.index') }}" icon="quiz" :active="request()->routeIs('survey.*')">
            Survey
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endcan

    @can('participants.read')
    <div class="category-header relative">
        <a href="{{ route('participants') }}" class="block relative">
            <div class="sidebar-link {{ request()->routeIs('participants') ? 'is-active' : '' }}">
                <div class="flex items-center">
                    <span class="material-icons-outlined sidebar-icon">people</span>
                    <p class="sidebar-label">Participants</p>
                </div>
            </div>
        </a>
    </div>
    @endcan

    {{-- Attendance --}}
    @if(auth()->user()->can('attendance_management.read') || auth()->user()->can('attendance.read') || auth()->user()->can('archives.read'))
    <div class="category-header relative" onclick="toggleSection('attendance-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">how_to_reg</span>
                <p class="sidebar-label">Attendance</p>
            </div>
            <svg class="sidebar-chevron" id="attendance-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="attendance-section" class="hierarchical-menu" style="display: none;">
        @can('attendance_management.read')
        <x-sidebar-submenu-item href="{{ route('attendance.index') }}" icon="fact_check" :active="request()->routeIs('attendance.index')">
            Manage Attendance
        </x-sidebar-submenu-item>
        @endcan
        @can('attendance.read')
        <x-sidebar-submenu-item href="{{ route('attendance.list') }}" icon="view_list" :active="request()->routeIs('attendance.list')">
            Attendance List
        </x-sidebar-submenu-item>
        @endcan
        @can('archives.read')
        <x-sidebar-submenu-item href="{{ route('attendance.archive') }}" icon="inventory" :active="request()->routeIs('attendance.archive')">
            Archive
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    {{-- Certificate Management --}}
    @if(auth()->user()->can('certificates.read') || auth()->user()->can('certificates.create') || auth()->user()->can('templates.read'))
    <div class="category-header relative" onclick="toggleSection('certificate-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">workspace_premium</span>
                <p class="sidebar-label">Certificate</p>
            </div>
            <svg class="sidebar-chevron" id="certificate-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="certificate-section" class="hierarchical-menu" style="display: none;">
        @can('certificates.read')
        <x-sidebar-submenu-item href="{{ route('certificates.index') }}" icon="list_alt" :active="request()->routeIs('certificates.index')">
            Manage Certificates
        </x-sidebar-submenu-item>
        @endcan
        @can('templates.read')
        <x-sidebar-submenu-item href="{{ route('template.designer') }}" icon="design_services" :active="request()->routeIs('template.*')">
            Template Designer
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    @if(auth()->user()->can('pwa_participants.read') || auth()->user()->can('pwa_analytics.read') || auth()->user()->can('pwa_templates.read') || auth()->user()->can('pwa_settings.read') || auth()->user()->can('campaigns.read') || auth()->user()->can('delivery.read'))
    <p class="sidebar-group-label">Engagement</p>
    @endif

    {{-- PWA Management --}}
    @if(auth()->user()->can('pwa_participants.read') || auth()->user()->can('pwa_analytics.read') || auth()->user()->can('pwa_templates.read') || auth()->user()->can('pwa_settings.read'))
    <div class="category-header relative" onclick="toggleSection('ecertificate-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">smartphone</span>
                <p class="sidebar-label">PWA Management</p>
            </div>
            <svg class="sidebar-chevron" id="ecertificate-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="ecertificate-section" class="hierarchical-menu" style="display: none;">
        @can('pwa_participants.read')
        <x-sidebar-submenu-item href="{{ route('pwa.participants') }}" icon="people" :active="request()->routeIs('pwa.participants')">
            Participants
        </x-sidebar-submenu-item>
        @endcan
        @can('pwa_analytics.read')
        <x-sidebar-submenu-item href="{{ route('pwa.analytics') }}" icon="analytics" :active="request()->routeIs('pwa.analytics')">
            Analytics
        </x-sidebar-submenu-item>
        @endcan
        @can('pwa_templates.read')
        <x-sidebar-submenu-item href="{{ route('pwa.templates') }}" icon="email" :active="request()->routeIs('pwa.templates')">
            Email Templates
        </x-sidebar-submenu-item>
        @endcan
        @can('pwa_settings.read')
        <x-sidebar-submenu-item href="{{ route('pwa.settings') }}" icon="settings" :active="request()->routeIs('pwa.settings')">
            Event Settings
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    {{-- Campaign --}}
    @if(auth()->user()->can('campaigns.read') || auth()->user()->can('delivery.read'))
    <div class="category-header relative" onclick="toggleSection('campaign-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">campaign</span>
                <p class="sidebar-label">Campaign</p>
            </div>
            <svg class="sidebar-chevron" id="campaign-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="campaign-section" class="hierarchical-menu" style="display: none;">
        @can('campaigns.read')
        <x-sidebar-submenu-item href="{{ route('campaign.index') }}" icon="campaign" :active="request()->routeIs('campaign.index')">
            Campaign
        </x-sidebar-submenu-item>
        @endcan
        @can('delivery.read')
        <x-sidebar-submenu-item href="{{ route('config.deliver') }}" icon="settings_applications" :active="request()->routeIs('config.deliver')">
            Config Delivery
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    @if(auth()->user()->can('attendance_reports.read') || auth()->user()->can('event_statistics.read') || auth()->user()->can('certificate_reports.read'))
    <p class="sidebar-group-label">Insights</p>
    {{-- Reports --}}
    <div class="category-header relative" onclick="toggleSection('reports-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">assessment</span>
                <p class="sidebar-label">Reports</p>
            </div>
            <svg class="sidebar-chevron" id="reports-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="reports-section" class="hierarchical-menu" style="display: none;">
        @can('attendance_reports.read')
        <x-sidebar-submenu-item href="{{ route('reports.attendance.index') }}" icon="summarize" :active="request()->routeIs('reports.attendance.index')">
            Attendance Reports
        </x-sidebar-submenu-item>
        @endcan
        @can('event_statistics.read')
        <x-sidebar-submenu-item href="{{ route('reports.statistics') }}" icon="insights" :active="request()->routeIs('reports.statistics')">
            Event Statistics
        </x-sidebar-submenu-item>
        @endcan
        @can('certificate_reports.read')
        <x-sidebar-submenu-item href="{{ route('reports.certificates') }}" icon="description" :active="request()->routeIs('reports.certificates')">
            Certificate Reports
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    @if(auth()->user()->can('helpdesk.read') || auth()->user()->can('global_config.read') || auth()->user()->can('roles.read') || auth()->user()->can('users.read') || auth()->user()->can('log_activity.read'))
    <p class="sidebar-group-label">System</p>
    @endif

    @can('helpdesk.read')
    <div class="category-header relative">
        <a href="{{ route('helpdesk.index') }}" class="block relative">
            <div class="sidebar-link {{ request()->routeIs('helpdesk.index') ? 'is-active' : '' }}">
                <div class="flex items-center">
                    <span class="material-icons-outlined sidebar-icon">help</span>
                    <p class="sidebar-label">Helpdesk</p>
                </div>
            </div>
        </a>
    </div>
    @endcan

    {{-- Settings --}}
    @if(auth()->user()->can('global_config.read') || auth()->user()->can('roles.read') || auth()->user()->can('users.read') || auth()->user()->can('log_activity.read'))
    <div class="category-header relative" onclick="toggleSection('settings-section', event)">
        <div class="sidebar-link">
            <div class="flex items-center">
                <span class="material-icons-outlined sidebar-icon">settings</span>
                <p class="sidebar-label">Settings</p>
            </div>
            <svg class="sidebar-chevron" id="settings-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" /></svg>
        </div>
    </div>
    <div id="settings-section" class="hierarchical-menu" style="display: none;">
        @can('global_config.read')
        <x-sidebar-submenu-item href="{{ route('settings.global-config') }}" icon="settings" :active="request()->routeIs('settings.global-config')">
            Global Config
        </x-sidebar-submenu-item>
        @endcan
        @can('roles.read')
        <x-sidebar-submenu-item href="{{ route('role.management') }}" icon="admin_panel_settings" :active="request()->routeIs('role.management')">
            Role Management
        </x-sidebar-submenu-item>
        @endcan
        @can('users.read')
        <x-sidebar-submenu-item href="{{ route('user.management') }}" icon="manage_accounts" :active="request()->routeIs('user.management')">
            User Management
        </x-sidebar-submenu-item>
        @endcan
        @can('log_activity.read')
        <x-sidebar-submenu-item href="{{ route('settings.log-activity') }}" icon="event_note" :active="request()->routeIs('settings.log-activity')">
            Log Activity
        </x-sidebar-submenu-item>
        @endcan
        @can('security_audit.read')
        <x-sidebar-submenu-item href="{{ route('settings.security-audit') }}" icon="security" :active="request()->routeIs('settings.security-audit')">
            Security &amp; Audit
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif

    <script>
        function toggleSection(sectionId, event) {
            // Clicks originating from a submenu link should navigate, not collapse
            if (event && event.target.closest('a')) {
                return;
            }

            const section = document.getElementById(sectionId);
            const icon = document.getElementById(sectionId + '-icon');

            if (section.style.display === 'none') {
                section.style.display = 'block';
                icon.style.transform = 'rotate(0deg)';
            } else {
                section.style.display = 'none';
                icon.style.transform = 'rotate(-90deg)';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Collapse every section by default
            document.querySelectorAll('.sidebar-chevron').forEach(icon => {
                icon.style.transform = 'rotate(-90deg)';
            });

            // Expand the section containing the active page
            document.querySelectorAll('.sidebar-submenu-item.active').forEach(submenu => {
                const parentMenu = submenu.closest('.hierarchical-menu');
                if (!parentMenu) {
                    return;
                }

                parentMenu.style.display = 'block';

                const icon = document.getElementById(parentMenu.id + '-icon');
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>
</x-sidebar>
