<x-sidebar>
    <!-- Main Navigation Items with Same Style as Categories -->
    <div class="category-header relative">
        <a href="{{ route('dashboard') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('dashboard') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-base text-blue-500 mr-3">dashboard</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Dashboard</p>
                </div>
            </div>
        </a>
    </div>
    
    @can('events.read')
    <div class="mt-2"></div>
    <div class="category-header relative" onclick="toggleSection('event-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-green-500 mr-3">event</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Event</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="event-section-icon">expand_more</span>
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
    <div class="mt-2"></div>
    <div class="category-header relative">
        <a href="{{ route('participants') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('participants') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-base text-purple-500 mr-3">people</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Participants</p>
                </div>
            </div>
        </a>
    </div>
    @endcan
    
    <!-- Attendance Section -->
    @if(auth()->user()->can('attendance_management.read') || auth()->user()->can('attendance.read') || auth()->user()->can('archives.read'))
    <div class="mt-2"></div>
    <div class="category-header relative" onclick="toggleSection('attendance-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-orange-500 mr-3">how_to_reg</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Attendance</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="attendance-section-icon">expand_more</span>
        </div>
    </div>
    <div id="attendance-section" class="hierarchical-menu" style="display: none;">
        @can('attendance_management.read')
        <x-sidebar-submenu-item href="{{ route('attendance.index') }}" icon="fact_check" :active="request()->routeIs('attendance.index')">
            Manage Attendance
        </x-sidebar-submenu-item>
        @endcan
        @can('attendance.read')
        <x-sidebar-submenu-item href="{{ route('attendance.list') }}" icon="view_list">
            Attendance List
        </x-sidebar-submenu-item>
        @endcan
        @can('archives.read')
        <x-sidebar-submenu-item href="{{ route('attendance.archive') }}" icon="inventory">
            Archive
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    <!-- Certificate Management -->
    @if(auth()->user()->can('certificates.read') || auth()->user()->can('certificates.create') || auth()->user()->can('templates.read'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('certificate-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-yellow-500 mr-3">workspace_premium</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Certificate</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="certificate-section-icon">expand_more</span>
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
    
    <!-- PWA Management Section -->
    @if(auth()->user()->can('pwa_participants.read') || auth()->user()->can('pwa_analytics.read') || auth()->user()->can('pwa_templates.read') || auth()->user()->can('pwa_settings.read'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('ecertificate-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-indigo-500 mr-3">smartphone</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">PWA Management</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="ecertificate-section-icon">expand_more</span>
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
    
    <!-- Reports Section -->
    @if(auth()->user()->can('attendance_reports.read') || auth()->user()->can('event_statistics.read') || auth()->user()->can('certificate_reports.read'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('reports-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-red-500 mr-3">assessment</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Reports</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="reports-section-icon">expand_more</span>
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
    
    <!-- Campaign Section -->
    @if(auth()->user()->can('campaigns.read') || auth()->user()->can('delivery.read'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('campaign-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-pink-500 mr-3">campaign</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Campaign</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="campaign-section-icon">expand_more</span>
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
    
    @can('helpdesk.read')
    <div class="mt-4"></div>
    <div class="category-header relative">
        <a href="{{ route('helpdesk.index') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('helpdesk.index') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-base text-teal-500 mr-3">help</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Helpdesk</p>
                </div>
            </div>
        </a>
    </div>
    @endcan
    
    <!-- Separator line after Helpdesk -->
    <div class="sidebar-separator"></div>
    
    <!-- Settings Section -->
    @if(auth()->user()->can('global_config.read') || auth()->user()->can('roles.read') || auth()->user()->can('users.read') || auth()->user()->can('log_activity.read'))
    <div class="category-header relative" onclick="toggleSection('settings-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-base text-cyan-600 mr-3">settings</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Settings</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="settings-section-icon">expand_more</span>
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
            Security & Audit
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    <script>
        function toggleSection(sectionId) {
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
        
        // Initialize sections - expand sections with active submenu
        document.addEventListener('DOMContentLoaded', function() {
            // All sections are already set to display:none in the HTML
            const icons = document.querySelectorAll('.category-header .material-icons');
            icons.forEach(icon => {
                if (icon.id && icon.id.includes('-icon')) {
                    icon.style.transform = 'rotate(-90deg)';
                }
            });
            
            // Check if any submenu is active and expand its parent section
            const activeSubmenus = document.querySelectorAll('.sidebar-submenu-item.active');
            activeSubmenus.forEach(submenu => {
                // Find parent hierarchical-menu
                const parentMenu = submenu.closest('.hierarchical-menu');
                if (parentMenu) {
                    // Get the section ID from the parent menu's ID
                    const sectionId = parentMenu.id;
                    
                    // Show the section
                    parentMenu.style.display = 'block';
                    
                    // Rotate the icon
                    const icon = document.getElementById(sectionId + '-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            });
        });
    </script>
</x-sidebar> 