<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance Reports</span>
    </x-slot>

    <x-slot name="title">Attendance Reports</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">assignment</span>
                        <h1 class="text-xl font-bold text-gray-800">Attendance Reports</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">View and export attendance data for events</p>
                </div>
                <div>
                    <a href="#" onclick="exportReport()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out ml-2">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            
            <!-- Attendance Summary -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Total Sessions</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $totalSessions }}</p>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Total Attendees</p>
                    <p class="text-2xl font-bold text-green-800">{{ $totalAttendees }}</p>
                </div>
                
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Average Attendance Rate</p>
                    <p class="text-2xl font-bold text-amber-800">{{ $averageAttendanceRate }}%</p>
                </div>
            </div>
            
            <!-- Show Entries & Filter Row -->
            <div class="mb-4">
                <form method="GET" action="{{ route('reports.attendance.index') }}" class="flex flex-wrap gap-2 items-center justify-between">
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search event name, location..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="event_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @if(request('event_filter') == $event->id) selected @endif class="truncate">{{ $event->name }}</option>
                            @endforeach
                        </select>
                        <select name="date_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Dates</option>
                            <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                            <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                            <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                            <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                        </select>
                        <select name="rate_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Rates</option>
                            <option value="high" @if(request('rate_filter') == 'high') selected @endif>High (>80%)</option>
                            <option value="medium" @if(request('rate_filter') == 'medium') selected @endif>Medium (50-80%)</option>
                            <option value="low" @if(request('rate_filter') == 'low') selected @endif>Low (<50%)</option>
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('event_filter') || request('date_filter') || request('rate_filter'))
                            <a href="{{ route('reports.attendance.index') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('event_filter') || request('date_filter') || request('rate_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('event_filter'))
                        <span class="ml-2">Event: {{ $events->find(request('event_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    @if(request('rate_filter'))
                        <span class="ml-2">Rate: {{ ucfirst(request('rate_filter')) }}</span>
                    @endif
                    <span class="ml-2">({{ $sessions->total() }} results)</span>
                </div>
            @endif
            
            <!-- Attendance Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                            <th class="py-3 px-4 text-left">Session Date</th>
                            <th class="py-3 px-4 text-left">Start Time</th>
                            <th class="py-3 px-4 text-left">End Time</th>
                            <th class="py-3 px-4 text-left">Registered</th>
                            <th class="py-3 px-4 text-left">Attended</th>
                            <th class="py-3 px-4 text-left">Attendance Rate</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tableRows as $row)
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">{{ $row['event_name'] }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y') }}</td>
                            <td class="py-3 px-4">{{ substr($row['start_time'],0,5) }}</td>
                            <td class="py-3 px-4">{{ substr($row['end_time'],0,5) }}</td>
                            <td class="py-3 px-4">{{ $row['registered'] }}</td>
                            <td class="py-3 px-4">{{ $row['attended'] }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $row['rate'] }}%"></div>
                                    </div>
                                    <span class="ml-2">{{ $row['rate'] }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('reports.attendance.show', ['id' => $row['id']]) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                    <form method="POST" action="{{ route('reports.attendance.export') }}" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $row['id'] }}">
                                        <button type="submit" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Export">
                                            <span class="material-icons text-green-600 text-xs">download</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('reports.attendance.delete', $row['id']) }}" onsubmit="return confirm('Are you sure you want to delete this attendance report?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                            <span class="material-icons text-red-600 text-xs">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="text-xs">
                            <td colspan="8" class="py-8 text-center text-gray-500">No attendance sessions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($sessions->total() > 0)
                        Showing <span class="font-medium">{{ $sessions->firstItem() }}</span> to <span class="font-medium">{{ $sessions->lastItem() }}</span> of <span class="font-medium">{{ $sessions->total() }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{-- Using Laravel's built-in pagination links --}}
                    {{ $sessions->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md mx-4 w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Confirm Deletion</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600">Are you sure you want to delete this attendance report? This action cannot be undone.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-xs">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for the page -->
    <script>
        // Debounce search input
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }
        
        function exportReport() {
            // Logic to export report
            alert('Exporting attendance report...');
        }

        function confirmDelete(id) {
            // Set the form action with the ID
            document.getElementById('deleteForm').action = `{{ route('reports.attendance.index') }}/${id}`;
            // Show the modal
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            // Hide the modal
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-app-layout> 