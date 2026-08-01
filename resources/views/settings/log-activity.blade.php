<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Log Activity</span>
    </x-slot>

    <x-slot name="title">Log Activity</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">event_note</span>
                        <h1 class="text-xl font-bold text-gray-800">Log Activity</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Monitor and track system activities</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Clear Logs Dropdown -->
                    @can('log_activity.delete')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">delete_sweep</span>
                            Clear Logs
                            <span class="material-icons-outlined text-xs ml-1">arrow_drop_down</span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                            <div class="py-1">
                                <button onclick="clearLogs('all')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">delete_forever</span>
                                    All Logs
                                </button>
                                <button onclick="clearLogs('30')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    30 Days
                                </button>
                                <button onclick="clearLogs('60')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    60 Days
                                </button>
                                <button onclick="clearLogs('90')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    90 Days
                                </button>
                                <button onclick="clearLogs('120')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="material-icons-outlined text-xs mr-2">schedule</span>
                                    120 Days
                                </button>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @can('log_activity.export')
                    <button class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">file_download</span>
                        Export Logs
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Log Summary -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Total Logs</p>
                    <p class="text-2xl font-bold text-blue-800">{{ \Spatie\Activitylog\Models\Activity::count() }}</p>
                    <p class="text-[10px] text-blue-600 mt-1">All recorded activities</p>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">User Activity</p>
                    <p class="text-2xl font-bold text-green-800">{{ \Spatie\Activitylog\Models\Activity::whereNotNull('causer_id')->count() }}</p>
                    <p class="text-[10px] text-green-600 mt-1">User-initiated actions</p>
                </div>
                
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">System Events</p>
                    <p class="text-2xl font-bold text-amber-800">{{ \Spatie\Activitylog\Models\Activity::whereNull('causer_id')->count() }}</p>
                    <p class="text-[10px] text-amber-600 mt-1">Automated system operations</p>
                </div>
                
                <div class="bg-red-50 rounded-md p-4 border border-red-100">
                    <p class="text-xs text-red-700 font-medium">Errors</p>
                    <p class="text-2xl font-bold text-red-800">{{ \Spatie\Activitylog\Models\Activity::where('event', 'like', '%error%')->orWhere('event', 'like', '%failed%')->count() }}</p>
                    <p class="text-[10px] text-red-600 mt-1">Errors and warnings</p>
                </div>
            </div>
            
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('settings.log-activity') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search description, log name, event..."
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

                @if(request('search') || request('log_name') || request('event') || request('date_filter'))
                    <a href="{{ route('settings.log-activity') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('log_name') || request('event') || request('date_filter'))
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
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $activities->total() }} results)</span>
                </div>
            @endif
            
            <!-- Logs Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">ID</th>
                            <th class="py-3 px-4 text-left">Timestamp</th>
                            <th class="py-3 px-4 text-left">User</th>
                            <th class="py-3 px-4 text-left">IP Address</th>
                            <th class="py-3 px-4 text-left">Action</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($activities as $activity)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">#LOG-{{ $activity->id }}</td>
                                <td class="py-3 px-4">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="py-3 px-4">{{ $activity->causer ? $activity->causer->email : 'System' }}</td>
                                <td class="py-3 px-4">{{ request()->ip() }}</td>
                                <td class="py-3 px-4">{{ $activity->description }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $activity->log_name ?: 'General' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">{{ $activity->event ?: 'Success' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center">
                                        <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details" onclick="showActivityDetails({{ $activity->id }})">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="8" class="py-8 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <span class="material-icons-outlined text-gray-400 text-4xl mb-2">event_note</span>
                                        <p class="text-sm">No activity logs found</p>
                                        <p class="text-xs text-gray-400 mt-1">Activity logs will appear here when users perform actions</p>
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
        </div>
    </div>
    
    <!-- Log Activity Details Modal -->
    <div id="logModal" class="fixed inset-0 modal-backdrop-glass flex items-center justify-center z-50" style="display: none;">
        <div id="logModalContent" class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
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

                // Activity details modal functionality
                window.showActivityDetails = function(activityId) {
                    // Show loading state
                    document.getElementById('logModal').style.display = 'flex';
                    document.getElementById('logModalContent').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-DEFAULT mx-auto"></div><p class="mt-2 text-sm text-gray-500">Loading activity details...</p></div>';
                    
                    // Fetch activity details via AJAX
                    fetch(`/settings/log-activity/${activityId}/details`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Populate modal with real data
                            document.getElementById('logModalContent').innerHTML = `
                                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                                    <h3 class="text-lg font-medium">Log Entry Details</h3>
                                    <button onclick="closeLogModal()" class="text-white hover:text-gray-200">
                                        <span class="material-icons">close</span>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="border-b border-gray-200 pb-4">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-xs font-medium text-gray-500">Log ID</p>
                                                    <p class="text-sm font-bold">#LOG-${data.id}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-medium text-gray-500">Timestamp</p>
                                                    <p class="text-sm">${data.timestamp}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">User</p>
                                                <p class="text-sm">${data.user}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">IP Address</p>
                                                <p class="text-sm">${data.ip_address}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Action</p>
                                                <p class="text-sm">${data.event}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Type</p>
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
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Log Name</p>
                                                <p class="text-sm">${data.log_name}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Event Type</p>
                                                <p class="text-sm">${data.event_type}</p>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-medium text-gray-500">Description</p>
                                            <p class="text-sm">${data.description}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-medium text-gray-500">Additional Data</p>
                                            <pre class="text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-auto max-h-40">${JSON.stringify(data.data, null, 2)}</pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <button onclick="closeLogModal()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                                        Close
                                    </button>
                                </div>
                            `;
                        })
                        .catch(error => {
                            console.error('Error fetching activity details:', error);
                            document.getElementById('logModalContent').innerHTML = `
                                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                                    <h3 class="text-lg font-medium">Error</h3>
                                    <button onclick="closeLogModal()" class="text-white hover:text-gray-200">
                                        <span class="material-icons">close</span>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <p class="text-sm text-red-600">Failed to load activity details. Please try again.</p>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <button onclick="closeLogModal()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                                        Close
                                    </button>
                                </div>
                            `;
                        });
                };

                // Close modal function
                window.closeLogModal = function() {
                    document.getElementById('logModal').style.display = 'none';
                };

                // Clear logs function
                window.clearLogs = function(days) {
                    let message = '';
                    let confirmMessage = '';
                    
                    if (days === 'all') {
                        message = 'Are you sure you want to clear ALL activity logs? This action cannot be undone.';
                        confirmMessage = 'Clear All Logs';
                    } else {
                        message = `Are you sure you want to clear activity logs older than ${days} days? This action cannot be undone.`;
                        confirmMessage = `Clear Logs (${days} Days)`;
                    }
                    
                    if (confirm(message)) {
                        // Show loading state
                        const button = event.target;
                        const originalText = button.innerHTML;
                        button.innerHTML = '<span class="material-icons-outlined text-xs mr-2 animate-spin">hourglass_empty</span>Clearing...';
                        button.disabled = true;
                        
                        // Make API call
                        fetch('/settings/log-activity/clear', {
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
                                showNotification(data.message, 'success');
                                // Reload page after 2 seconds
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                showNotification(data.message, 'error');
                                // Reset button
                                button.innerHTML = originalText;
                                button.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error clearing logs:', error);
                            showNotification('Failed to clear logs. Please try again.', 'error');
                            // Reset button
                            button.innerHTML = originalText;
                            button.disabled = false;
                        });
                    }
                };

                // Notification function
                function showNotification(message, type = 'info') {
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