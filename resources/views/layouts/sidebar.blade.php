<x-sidebar>
    <!-- Main Navigation Items with Same Style as Categories -->
    <div class="category-header relative">
        <a href="{{ route('dashboard') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('dashboard') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-xs text-gray-500 mr-3">dashboard</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Dashboard</p>
                </div>
            </div>
        </a>
    </div>
    
    @can('view_events')
    <div class="mt-2"></div>
    <div class="category-header relative">
        <a href="{{ route('event.management') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('event.management') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-xs text-gray-500 mr-3">event</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Event Management</p>
                </div>
            </div>
        </a>
    </div>
    @endcan
    
    @can('view_participants')
    <div class="mt-2"></div>
    <div class="category-header relative">
        <a href="{{ route('participants') }}" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 {{ request()->routeIs('participants') ? 'bg-blue-100' : '' }} relative">
                <div class="flex items-center">
                    <span class="material-icons text-xs text-gray-500 mr-3">people</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Participants</p>
                </div>
            </div>
        </a>
    </div>
    @endcan
    
    <!-- Attendance Section -->
    @if(auth()->user()->can('view_attendance') || auth()->user()->can('manage_attendance') || auth()->user()->can('view_archives'))
    <div class="mt-2"></div>
    <div class="category-header relative" onclick="toggleSection('attendance-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-xs text-gray-500 mr-3">how_to_reg</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Attendance</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="attendance-section-icon">expand_more</span>
        </div>
    </div>
    <div id="attendance-section" class="hierarchical-menu" style="display: none;">
        @can('manage_attendance')
        <x-sidebar-submenu-item href="{{ route('attendance.index') }}" icon="fact_check" :active="request()->routeIs('attendance.index')">
            Manage Attendance
        </x-sidebar-submenu-item>
        @endcan
        @can('view_attendance')
        <x-sidebar-submenu-item href="{{ route('attendance.list') }}" icon="view_list">
            Attendance List
        </x-sidebar-submenu-item>
        @endcan
        @can('view_archives')
        <x-sidebar-submenu-item href="#" icon="inventory">
            Archive
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    <!-- Certificate Management -->
    @if(auth()->user()->can('view_certificates') || auth()->user()->can('generate_certificates') || auth()->user()->can('edit_templates'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('certificate-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-xs text-gray-500 mr-3">workspace_premium</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Certificate</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="certificate-section-icon">expand_more</span>
        </div>
    </div>
    <div id="certificate-section" class="hierarchical-menu" style="display: none;">
        @can('view_certificates')
        <x-sidebar-submenu-item href="{{ route('certificates.index') }}" icon="list_alt" :active="request()->routeIs('certificates.index')">
            Manage Certificates
        </x-sidebar-submenu-item>
        @endcan
        
        @can('generate_certificates')
        <x-sidebar-submenu-item href="{{ route('certificates.create') }}" icon="add_circle" :active="request()->routeIs('certificates.create')">
            Generate Certificates
        </x-sidebar-submenu-item>
        @endcan
        
        @can('edit_templates')
        <x-sidebar-submenu-item href="{{ route('template.designer') }}" icon="design_services" :active="request()->routeIs('template.*')">
            Template Designer
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    <!-- Reports Section -->
    @if(auth()->user()->can('view_attendance_reports') || auth()->user()->can('view_event_statistics') || auth()->user()->can('view_certificate_reports'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('reports-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-xs text-gray-500 mr-3">assessment</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Reports</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="reports-section-icon">expand_more</span>
        </div>
    </div>
    <div id="reports-section" class="hierarchical-menu" style="display: none;">
        @can('view_attendance_reports')
        <x-sidebar-submenu-item href="#" icon="summarize">
            Attendance Reports
        </x-sidebar-submenu-item>
        @endcan
        @can('view_event_statistics')
        <x-sidebar-submenu-item href="#" icon="insights">
            Event Statistics
        </x-sidebar-submenu-item>
        @endcan
        @can('view_certificate_reports')
        <x-sidebar-submenu-item href="#" icon="description">
            Certificate Reports
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    <!-- Campaign Section -->
    @if(auth()->user()->can('view_campaigns') || auth()->user()->can('view_database_users') || auth()->user()->can('manage_delivery'))
    <div class="mt-4"></div>
    <div class="category-header relative" onclick="toggleSection('campaign-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-xs text-gray-500 mr-3">campaign</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Campaign</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="campaign-section-icon">expand_more</span>
        </div>
    </div>
    <div id="campaign-section" class="hierarchical-menu" style="display: none;">
        @can('view_campaigns')
        <x-sidebar-submenu-item href="#" icon="campaign">
            Campaign
        </x-sidebar-submenu-item>
        @endcan
        @can('view_database_users')
        <x-sidebar-submenu-item href="#" icon="group">
            Database User
        </x-sidebar-submenu-item>
        @endcan
        @can('manage_delivery')
        <x-sidebar-submenu-item href="#" icon="settings_applications">
            Config Delivery
        </x-sidebar-submenu-item>
        @endcan
    </div>
    @endif
    
    @can('view_helpdesk')
    <div class="mt-4"></div>
    <div class="category-header relative">
        <a href="#" class="block relative">
            <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
                <div class="flex items-center">
                    <span class="material-icons text-xs text-gray-500 mr-3">help</span>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Helpdesk</p>
                </div>
            </div>
        </a>
    </div>
    @endcan
    
    <!-- Separator line after Helpdesk -->
    <div class="sidebar-separator"></div>
    
    <!-- Settings Section -->
    @if(auth()->user()->can('view_settings') || auth()->user()->can('view_roles') || auth()->user()->can('view_users'))
    <div class="category-header relative" onclick="toggleSection('settings-section')">
        <div class="px-4 py-2 flex justify-between items-center cursor-pointer hover:bg-blue-50 relative">
            <div class="flex items-center">
                <span class="material-icons text-xs text-gray-500 mr-3">settings</span>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Settings</p>
            </div>
            <span class="material-icons text-xs text-gray-500 transform transition-transform duration-200" id="settings-section-icon">expand_more</span>
        </div>
    </div>
    <div id="settings-section" class="hierarchical-menu" style="display: none;">
        @can('manage_settings')
        <x-sidebar-submenu-item href="#" icon="settings">
            Global Config
        </x-sidebar-submenu-item>
        @endcan
        @can('view_roles')
        <x-sidebar-submenu-item href="{{ route('role.management') }}" icon="admin_panel_settings" :active="request()->routeIs('role.management')">
            Role Management
        </x-sidebar-submenu-item>
        @endcan
        @can('view_users')
        <x-sidebar-submenu-item href="{{ route('user.management') }}" icon="manage_accounts" :active="request()->routeIs('user.management')">
            User Management
        </x-sidebar-submenu-item>
        @endcan
        @can('view_settings')
        <x-sidebar-submenu-item href="#" icon="event_note">
            Log Activity
        </x-sidebar-submenu-item>
        <x-sidebar-submenu-item href="#" icon="security">
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