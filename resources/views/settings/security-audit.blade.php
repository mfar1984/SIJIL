<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Security & Audit</span>
    </x-slot>

    <x-slot name="title">Security & Audit</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">security</span>
                        <h1 class="text-xl font-bold text-gray-800">Security & Audit</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Monitor and manage system security and audit trails</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Clear Security Logs Dropdown -->
                    @can('security_audit.delete')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">delete_sweep</span>
                            Clear Security Logs
                            <span class="material-icons-outlined text-xs ml-1">arrow_drop_down</span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                            <div class="py-1">
                                <button onclick="clearSecurityLogs('all')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">delete_forever</span>
                                    All Security Logs
                                </button>
                                <button onclick="clearSecurityLogs('30')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    30 Days
                                </button>
                                <button onclick="clearSecurityLogs('60')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    60 Days
                                </button>
                                <button onclick="clearSecurityLogs('90')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    90 Days
                                </button>
                                <button onclick="clearSecurityLogs('120')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    120 Days
                                </button>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @can('security_audit.export')
                    <button class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">download</span>
                        Export Report
                    </button>
                    @endcan
                    <button class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">refresh</span>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            {{-- Every card is measured inside the same scope as the table and honours
                 the same filters. They used to search the entire activity log and
                 ignore the filters, which is why the total said 30 while the pager
                 underneath it said 44. --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                    ['Security events', $stats['total'], 'security', 'matching the current filters'],
                    ['Sign-ins', $stats['sign_ins'], 'login', 'successful authentications'],
                    ['Failed or suspicious', $stats['failed'], 'gpp_maybe', $stats['failed'] === 0 ? 'nothing recorded' : 'worth a look'],
                    ['Role and permission changes', $stats['role_changes'], 'admin_panel_settings', 'who can do what'],
                ] as [$label, $value, $icon, $note])
                    <div class="border border-gray-200 rounded p-4">
                        <div class="flex items-start justify-between">
                            <p class="text-xs text-gray-500">{{ $label }}</p>
                            <span class="material-icons-outlined text-gray-300 text-base">{{ $icon }}</span>
                        </div>
                        <p class="text-2xl font-bold {{ $label === 'Failed or suspicious' && $value > 0 ? 'text-red-600' : 'text-gray-800' }} mt-0.5">
                            {{ number_format($value) }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">{{ $note }}</p>
                    </div>
                @endforeach
            </div>
            
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('settings.security-audit') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search security events..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="log_name" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Log Names</option>
                    @foreach($logNames as $logName)
                        <option value="{{ $logName }}" @if(request('log_name') == $logName) selected @endif>{{ $logName }}</option>
                    @endforeach
                </select>

                <select name="event" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}" @if(request('event') == $event) selected @endif>{{ $event }}</option>
                    @endforeach
                </select>

                <select name="severity" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Severity</option>
                    <option value="high" @if(request('severity') == 'high') selected @endif>High</option>
                    <option value="medium" @if(request('severity') == 'medium') selected @endif>Medium</option>
                    <option value="low" @if(request('severity') == 'low') selected @endif>Low</option>
                </select>

                <select name="date_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Dates</option>
                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                    <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                    <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                    <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('log_name') || request('event') || request('severity') || request('date_filter'))
                    <a href="{{ route('settings.security-audit') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('log_name') || request('event') || request('severity') || request('date_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('log_name'))
                        <span class="ml-2">Log Name: {{ request('log_name') }}</span>
                    @endif
                    @if(request('event'))
                        <span class="ml-2">Event: {{ request('event') }}</span>
                    @endif
                    @if(request('severity'))
                        <span class="ml-2">Severity: {{ ucfirst(request('severity')) }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $activities->total() }} results)</span>
                </div>
            @endif

            {{-- Tabs are links, not client-side toggles.
                 They used to switch between four collections that had been fetched
                 in full and unfiltered, while the pager below described a fifth
                 query nobody could see. Now the tab is part of the request, so the
                 count on the tab, the rows in the table and the pager underneath are
                 all the same query. --}}
            @php
                $tabIcons = [
                    'all' => 'security',
                    'auth' => 'vpn_key',
                    'role' => 'admin_panel_settings',
                    'user' => 'person',
                ];
            @endphp

            <div class="mb-4">
                <div class="border-b border-gray-200">
                    <nav class="flex flex-wrap -mb-px">
                        @foreach($tabs as $key => $label)
                            @php $active = $tab === $key; @endphp
                            <a href="{{ route('settings.security-audit', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
                               class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 transition duration-150 ease-in-out
                                      {{ $active ? 'text-primary-DEFAULT border-primary-DEFAULT' : 'text-gray-500 hover:text-primary-DEFAULT border-transparent' }}">
                                <span class="material-icons-outlined text-sm mr-1.5">{{ $tabIcons[$key] ?? 'label' }}</span>
                                {{ $label }}
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] {{ $active ? 'bg-primary-DEFAULT text-white' : 'bg-gray-100 text-gray-600' }}">
                                    {{ number_format($tabCounts[$key]) }}
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
            
            {{-- One table, showing the rows of the query that the tab count and the
                 pager below both describe. There were four tables here, each fed by
                 its own unfiltered ->get(), which is why a tab could show one row
                 while the footer reported 44 across five pages. --}}
            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">ID</th>
                            <th class="py-3 px-4 text-left">When</th>
                            <th class="py-3 px-4 text-left">Who</th>
                            <th class="py-3 px-4 text-left">What happened</th>
                            <th class="py-3 px-4 text-left">Category</th>
                            <th class="py-3 px-4 text-left">Outcome</th>
                            <th class="py-3 px-4 text-center rounded-tr">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($activities as $activity)
                            @php
                                $text = strtolower((string) $activity->description);
                                $failed = str_contains($text, 'failed')
                                    || str_contains($text, 'unauthorized')
                                    || str_contains($text, 'suspicious')
                                    || str_contains($text, 'banned');

                                $category = match ($activity->log_name) {
                                    'auth' => ['Authentication', 'bg-blue-100 text-blue-800'],
                                    'security' => ['Security alert', 'bg-red-100 text-red-800'],
                                    'user' => ['User management', 'bg-purple-100 text-purple-800'],
                                    'role' => ['Role management', 'bg-amber-100 text-amber-800'],
                                    default => [$activity->log_name ?: 'General', 'bg-gray-100 text-gray-700'],
                                };

                                // The IP column used to print request()->ip(), which is the
                                // address of whoever is reading the page, repeated on every
                                // row. It is only shown when the entry actually recorded one.
                                $properties = $activity->properties ?? collect();
                                $ip = is_object($properties) ? ($properties['ip'] ?? $properties['ip_address'] ?? null) : null;
                            @endphp
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-500">#{{ $activity->id }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                                        {{ $activity->created_at->format('j M Y, H:i') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    {{ $activity->causer->email ?? 'System' }}
                                    @if($ip)
                                        <span class="block text-[10px] text-gray-400">{{ $ip }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $activity->description }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $category[1] }}">{{ $category[0] }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($failed)
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Failed</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Success</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center">
                                        <button type="button"
                                                class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100"
                                                title="View details"
                                                onclick="showSecurityDetails({{ $activity->id }})">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="7" class="py-10 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <span class="material-icons-outlined text-gray-300 text-4xl mb-2">security</span>
                                        <p class="text-sm">Nothing recorded for this tab.</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            @if(request()->hasAny(['search', 'log_name', 'event', 'severity', 'date_filter']))
                                                Clear the filters to see more.
                                            @else
                                                Entries appear here as people sign in and permissions change.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($activities->total() > 0)
                        Showing <span class="font-medium">{{ $activities->firstItem() }}</span> to <span class="font-medium">{{ $activities->lastItem() }}</span> of <span class="font-medium">{{ $activities->total() }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $activities->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Detail Modal -->
    <div x-data="{ showModal: false, securityDetails: {} }">
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop-glass"
            style="display: none;"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showModal = false">
                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                    <h3 class="text-lg font-medium">Security Event Details</h3>
                    <button @click="showModal = false" class="text-white hover:text-gray-200">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="border-b border-gray-200 pb-4">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Event ID</p>
                                    <p class="text-sm font-bold" x-text="securityDetails.id || '#SEC-1001'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Timestamp</p>
                                    <p class="text-sm" x-text="securityDetails.timestamp || '2023-06-15 08:15:22'"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">User</p>
                                <p class="text-sm" x-text="securityDetails.user || 'admin@example.com'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">IP Address</p>
                                <p class="text-sm" x-text="securityDetails.ip || '192.168.1.100'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Event</p>
                                <p class="text-sm" x-text="securityDetails.event || 'Successful Login'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Category</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs" x-text="securityDetails.category || 'Authentication'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs" x-text="securityDetails.status || 'Success'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">User Agent</p>
                                <p class="text-sm" x-text="securityDetails.userAgent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'"></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Description</p>
                            <p class="text-sm" x-text="securityDetails.description || 'User successfully authenticated to the system.'"></p>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Security Data</p>
                            <pre class="text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-auto max-h-40" x-text="securityDetails.data || JSON.stringify({auth_method: 'password', browser: 'Chrome', os: 'Windows', session_id: 'sess_abc123', '2fa_used': false}, null, 2)"></pre>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button @click="showModal = false" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Security Details Modal -->
        <div id="securityModal" class="fixed inset-0 modal-backdrop-glass flex items-center justify-center z-50" style="display: none;">
            <div id="modalContent" class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal content will be populated by JavaScript -->
            </div>
        </div>
        
        <script>
                // Search debounce functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchInput');
                    let searchTimeout;

                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(() => {
                                this.form.submit();
                            }, 500);
                        });
                    }

                    // Security details modal functionality
                    window.showSecurityDetails = function(activityId) {
                        // Show loading state
                        document.getElementById('securityModal').style.display = 'flex';
                        document.getElementById('modalContent').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-DEFAULT mx-auto"></div><p class="mt-2 text-sm text-gray-500">Loading security details...</p></div>';
                        
                        // Fetch activity details via AJAX
                        fetch(`/settings/security-audit/${activityId}/details`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                // Populate modal with real data
                                document.getElementById('modalContent').innerHTML = `
                                    <div class="px-6 py-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Security Activity Details</h3>
                                            <button onclick="closeSecurityModal()" class="text-gray-400 hover:text-gray-600">
                                                <span class="material-icons-outlined text-xl">close</span>
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Activity ID</p>
                                                <p class="text-sm font-medium">#SEC-${data.id}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Timestamp</p>
                                                <p class="text-sm">${data.timestamp}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">User</p>
                                                <p class="text-sm">${data.user}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">IP Address</p>
                                                <p class="text-sm">${data.ip_address}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Category</p>
                                                <p class="text-sm">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">${data.category}</span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Status</p>
                                                <p class="text-sm">
                                                    <span class="px-2 py-1 ${data.status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} rounded-full text-xs">${data.status}</span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">User Agent</p>
                                                <p class="text-sm text-xs">${data.user_agent}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <p class="text-xs font-medium text-gray-500">Description</p>
                                            <p class="text-sm">${data.description}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-medium text-gray-500">Security Data</p>
                                            <pre class="text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-auto max-h-40">${JSON.stringify(data.data, null, 2)}</pre>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                        <button onclick="closeSecurityModal()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                                            Close
                                        </button>
                                    </div>
                                `;
                            })
                            .catch(error => {
                                console.error('Error fetching security details:', error);
                                document.getElementById('modalContent').innerHTML = `
                                    <div class="px-6 py-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Error</h3>
                                            <button onclick="closeSecurityModal()" class="text-gray-400 hover:text-gray-600">
                                                <span class="material-icons-outlined text-xl">close</span>
                                            </button>
                                        </div>
                                        <p class="text-sm text-red-600">Failed to load security details. Please try again.</p>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                        <button onclick="closeSecurityModal()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                                            Close
                                        </button>
                                    </div>
                                `;
                            });
                    };

                    // Close modal function
                    window.closeSecurityModal = function() {
                        document.getElementById('securityModal').style.display = 'none';
                    };

                    // The tab switching script that used to sit here has been removed
                    // along with the four hidden tables it toggled. Tabs are links
                    // now, so the active tab is part of the request and the rows, the
                    // count on the tab and the pager all come from one query. The
                    // script also ran switchTab('security-events') on every load,
                    // which after this change would have had nothing to act on.

                    // Clear security logs function
                    window.clearSecurityLogs = function(days) {
                        let message = '';
                        let confirmMessage = '';
                        
                        if (days === 'all') {
                            message = 'Are you sure you want to clear ALL security audit logs? This action cannot be undone.';
                            confirmMessage = 'Clear All Security Logs';
                        } else {
                            message = `Are you sure you want to clear security audit logs older than ${days} days? This action cannot be undone.`;
                            confirmMessage = `Clear Security Logs (${days} Days)`;
                        }
                        
                        if (confirm(message)) {
                            // Show loading state
                            const button = event.target;
                            const originalText = button.innerHTML;
                            button.innerHTML = '<span class="material-icons-outlined text-xs mr-2 animate-spin">hourglass_empty</span>Clearing...';
                            button.disabled = true;
                            
                            // Make API call
                            fetch('/settings/security-audit/clear', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ days: days })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show success message
                                    showSecurityNotification(data.message, 'success');
                                    // Reload page after 2 seconds
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    showSecurityNotification(data.message, 'error');
                                    // Reset button
                                    button.innerHTML = originalText;
                                    button.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error clearing security logs:', error);
                                showSecurityNotification('Failed to clear security logs. Please try again.', 'error');
                                // Reset button
                                button.innerHTML = originalText;
                                button.disabled = false;
                            });
                        }
                    };

                    // Security notification function
                    function showSecurityNotification(message, type = 'info') {
                        const notification = document.createElement('div');
                        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-md shadow-lg text-white text-sm font-medium ${
                            type === 'success' ? 'bg-green-500' : 
                            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
                        }`;
                        notification.textContent = message;
                        
                        document.body.appendChild(notification);
                        
                        // Auto remove after 5 seconds
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 5000);
                    }
            });
        </script>
    </div>
</x-app-layout> 