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
            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="event_filter" class="block text-xs font-medium text-gray-700 mb-1">Event</label>
                    <select id="event_filter" name="event_filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ (isset($eventId) && $eventId == $event->id) ? 'selected' : '' }}>{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_range" class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                    <input type="text" id="date_range" name="date_range" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Select date range">
                </div>
                
                <div class="flex items-end">
                    <button type="button" onclick="filterAttendance()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">filter_list</span>
                        Apply Filter
                    </button>
                    <button type="button" onclick="resetFilters()" class="ml-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">refresh</span>
                        Reset
                    </button>
                </div>
            </div>
            
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
        function filterAttendance() {
            // Get filter values
            const eventFilter = document.getElementById('event_filter').value;
            const dateRange = document.getElementById('date_range').value;
            
            // Build query string
            let queryParams = [];
            if (eventFilter) {
                queryParams.push(`event_filter=${eventFilter}`);
            }
            if (dateRange) {
                queryParams.push(`date_range=${encodeURIComponent(dateRange)}`);
            }
            
            // Redirect with query parameters
            const queryString = queryParams.length > 0 ? `?${queryParams.join('&')}` : '';
            window.location.href = `{{ route('reports.attendance.index') }}${queryString}`;
        }
        
        function resetFilters() {
            // Reset form fields
            document.getElementById('event_filter').value = '';
            document.getElementById('date_range').value = '';
            
            // Redirect to base URL without query parameters
            window.location.href = "{{ route('reports.attendance.index') }}";
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
        
        // Initialize date range picker if available
        document.addEventListener('DOMContentLoaded', function() {
            // Check if date range picker library is available
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_range', {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    // Set initial value if exists in URL params
                    defaultDate: "{{ request('date_range') }}"
                });
            }
        });
    </script>
</x-app-layout> 