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
                        <span class="material-icons mr-2 text-primary-DEFAULT">event_note</span>
                        <h1 class="text-xl font-bold text-gray-800">Log Activity</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Monitor and track system activities</p>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">delete_sweep</span>
                        Clear Logs
                    </button>
                    <button class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Logs
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Show Entries & Filter Row -->
            <div class="mb-4">
                <form method="GET" action="{{ route('settings.log-activity') }}" class="flex flex-wrap gap-2 items-center justify-between">
                    <!-- Show Entries Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 font-medium">Show</span>
                        <select name="per_page" onchange="this.form.submit()" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                            <option value="10" @if(request('per_page', 10) == 10) selected @endif>10</option>
                            <option value="25" @if(request('per_page') == 25) selected @endif>25</option>
                            <option value="50" @if(request('per_page') == 50) selected @endif>50</option>
                            <option value="100" @if(request('per_page') == 100) selected @endif>100</option>
                        </select>
                        <span class="text-xs text-gray-600">entries per page</span>
                    </div>
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description, log name, event..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="log_name" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[140px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Log Names</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" @if(request('log_name') == $logName) selected @endif>{{ $logName }}</option>
                            @endforeach
                        </select>
                        <select name="event" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" @if(request('event') == $event) selected @endif>{{ $event }}</option>
                            @endforeach
                        </select>
                        <select name="date_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Dates</option>
                            <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                            <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                            <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                            <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('log_name') || request('event') || request('date_filter'))
                            <a href="{{ route('settings.log-activity') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            
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
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="8" class="py-8 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <span class="material-icons text-gray-400 text-4xl mb-2">event_note</span>
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
                        Showing <span class="font-medium">{{ $activities->firstItem() }}</span> to <span class="font-medium">{{ $activities->lastItem() }}</span> of <span class="font-medium">{{ $activities->total() }}</span> entries ({{ request('per_page', 10) }} per page)
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
    
    <!-- Log Detail Modal -->
    <div x-data="{ showModal: false, logDetails: {} }">
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showModal = false">
                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                    <h3 class="text-lg font-medium">Log Entry Details</h3>
                    <button @click="showModal = false" class="text-white hover:text-gray-200">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="border-b border-gray-200 pb-4">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Log ID</p>
                                    <p class="text-sm font-bold" x-text="logDetails.id || '#LOG-1001'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Timestamp</p>
                                    <p class="text-sm" x-text="logDetails.timestamp || '2023-06-15 09:32:45'"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">User</p>
                                <p class="text-sm" x-text="logDetails.user || 'admin@example.com'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">IP Address</p>
                                <p class="text-sm" x-text="logDetails.ip || '192.168.1.100'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Action</p>
                                <p class="text-sm" x-text="logDetails.action || 'User Login'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Type</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs" x-text="logDetails.type || 'Authentication'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs" x-text="logDetails.status || 'Success'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">User Agent</p>
                                <p class="text-sm" x-text="logDetails.userAgent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'"></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Description</p>
                            <p class="text-sm" x-text="logDetails.description || 'User successfully logged in to the system.'"></p>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Additional Data</p>
                            <pre class="text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-auto max-h-40" x-text="logDetails.data || '{\n  \"browser\": \"Chrome\",\n  \"os\": \"Windows\",\n  \"session_id\": \"sess_12345\",\n  \"login_method\": \"password\"\n}'"></pre>
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
                    // You can implement AJAX call here to get activity details
                    console.log('Showing details for activity:', activityId);
                    // For now, we'll just show a simple alert
                    alert('Activity details for ID: ' + activityId + '\n\nThis would show detailed information about the activity in a modal.');
                };
            });
        </script>
    </div>
</x-app-layout> 