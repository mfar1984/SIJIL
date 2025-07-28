<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Event Statistics</span>
    </x-slot>

    <x-slot name="title">Event Statistics</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">insights</span>
                        <h1 class="text-xl font-bold text-gray-800">Event Statistics</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Analyze event performance and trends</p>
                </div>
                <div>
                    <a href="#" onclick="exportStatistics()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out ml-2">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Statistics
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            
            <!-- Summary Cards -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full mr-4">
                            <span class="material-icons text-blue-600">event</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Total Events</p>
                            <p class="text-xl font-bold text-gray-800">{{ number_format($totalEvents) }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs {{ $eventPercentChange >= 0 ? 'text-green-600' : 'text-red-600' }} flex items-center">
                        <span class="material-icons text-xs mr-1">{{ $eventPercentChange >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span>{{ abs($eventPercentChange) }}% {{ $eventPercentChange >= 0 ? 'increase' : 'decrease' }} from previous period</span>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <span class="material-icons text-green-600">groups</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Total Participants</p>
                            <p class="text-xl font-bold text-gray-800">{{ number_format($totalParticipants) }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs {{ $participantPercentChange >= 0 ? 'text-green-600' : 'text-red-600' }} flex items-center">
                        <span class="material-icons text-xs mr-1">{{ $participantPercentChange >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span>{{ abs($participantPercentChange) }}% {{ $participantPercentChange >= 0 ? 'increase' : 'decrease' }} from previous period</span>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 bg-amber-100 rounded-full mr-4">
                            <span class="material-icons text-amber-600">emoji_events</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Certificates Issued</p>
                            <p class="text-xl font-bold text-gray-800">{{ number_format($totalCertificates) }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs {{ $certificatePercentChange >= 0 ? 'text-green-600' : 'text-red-600' }} flex items-center">
                        <span class="material-icons text-xs mr-1">{{ $certificatePercentChange >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span>{{ abs($certificatePercentChange) }}% {{ $certificatePercentChange >= 0 ? 'increase' : 'decrease' }} from previous period</span>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full mr-4">
                            <span class="material-icons text-purple-600">trending_up</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Avg. Attendance Rate</p>
                            <p class="text-xl font-bold text-gray-800">{{ $avgAttendanceRate }}%</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs {{ $attendanceRatePercentChange >= 0 ? 'text-green-600' : 'text-red-600' }} flex items-center">
                        <span class="material-icons text-xs mr-1">{{ $attendanceRatePercentChange >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span>{{ abs($attendanceRatePercentChange) }}% {{ $attendanceRatePercentChange >= 0 ? 'increase' : 'decrease' }} from previous period</span>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Events by Month Chart -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Events by Month</h3>
                    <div class="h-64">
                        <canvas id="eventsChart"></canvas>
                    </div>
                </div>
                
                <!-- Attendance Rate Chart -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Attendance Rate by Event Type</h3>
                    <div class="h-64 px-4">
                        @foreach($attendanceByType as $type => $rate)
                            <div class="h-8 flex items-center mb-2">
                                <p class="w-24 text-xs">{{ ucfirst($type) }}</p>
                                <div class="flex-1 h-6 bg-gray-200 rounded-full">
                                    <div class="h-6 bg-green-600 rounded-full" style="width: {{ $rate }}%"></div>
                                </div>
                                <p class="ml-2 w-8 text-xs font-medium">{{ $rate }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Top Events Table -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700">Top Performing Events</h3>
                </div>
                
                <!-- Show Entries & Filter Row -->
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('reports.statistics') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <!-- Show Entries Dropdown (Left) -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600 font-medium">Show</span>
                            <select name="per_page" onchange="this.form.submit()" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                                <option value="5" @if(request('per_page', 5) == 5) selected @endif>5</option>
                                <option value="10" @if(request('per_page') == 10) selected @endif>10</option>
                                <option value="25" @if(request('per_page') == 25) selected @endif>25</option>
                                <option value="50" @if(request('per_page') == 50) selected @endif>50</option>
                            </select>
                            <span class="text-xs text-gray-600">entries per page</span>
                        </div>
                        
                        <!-- Search & Filter Controls (Right) -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search event name, location..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                            <select name="event_type" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                                <option value="">All Types</option>
                                <option value="Conference" @if(request('event_type') == 'Conference') selected @endif>Conference</option>
                                <option value="Workshop" @if(request('event_type') == 'Workshop') selected @endif>Workshop</option>
                                <option value="Training" @if(request('event_type') == 'Training') selected @endif>Training</option>
                                <option value="Seminar" @if(request('event_type') == 'Seminar') selected @endif>Seminar</option>
                                <option value="Gaming" @if(request('event_type') == 'Gaming') selected @endif>Gaming</option>
                            </select>
                            <select name="status_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                                <option value="">All Status</option>
                                <option value="active" @if(request('status_filter') == 'active') selected @endif>Active</option>
                                <option value="completed" @if(request('status_filter') == 'completed') selected @endif>Completed</option>
                                <option value="cancelled" @if(request('status_filter') == 'cancelled') selected @endif>Cancelled</option>
                            </select>
                            <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                                </svg>
                            </button>
                            @if(request('search') || request('event_type') || request('status_filter'))
                                <a href="{{ route('reports.statistics') }}?per_page={{ request('per_page', 5) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
                
                <!-- Search Results Summary -->
                @if(request('search') || request('event_type') || request('status_filter'))
                    <div class="px-4 py-2 bg-blue-50 border-b border-blue-200 text-blue-700 text-xs">
                        <span class="font-medium">Search Results:</span>
                        @if(request('search'))
                            <span class="ml-2">Searching for "{{ request('search') }}"</span>
                        @endif
                        @if(request('event_type'))
                            <span class="ml-2">Type: {{ request('event_type') }}</span>
                        @endif
                        @if(request('status_filter'))
                            <span class="ml-2">Status: {{ ucfirst(request('status_filter')) }}</span>
                        @endif
                        <span class="ml-2">({{ $events->total() }} results)</span>
                    </div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-primary-light text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Event Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Participants</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Attendance Rate</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Certificates</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-xs">
                            @forelse($topEvents as $event)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800">{{ $event['name'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $event['date'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ ucfirst($event['type']) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ number_format($event['participants']) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-green-600 font-medium">{{ $event['attendance_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ number_format($event['certificates']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">No events found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                            @if($events->total() > 0)
                                Showing <span class="font-medium">{{ $events->firstItem() }}</span> to <span class="font-medium">{{ $events->lastItem() }}</span> of <span class="font-medium">{{ $events->total() }}</span> entries
                            @else
                                Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                            @endif
                        </div>
                        <div class="flex justify-end">
                            {{ $events->appends(request()->query())->links('components.pagination-modern') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for the page -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize monthly events chart
        const eventsChartCtx = document.getElementById('eventsChart').getContext('2d');
        const eventsChart = new Chart(eventsChartCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($monthlyEvents as $month)
                        '{{ $month['month'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Events',
                    data: [
                        @foreach($monthlyEvents as $month)
                            {{ $month['count'] }},
                        @endforeach
                    ],
                    backgroundColor: '#3b82f6',
                    borderColor: '#2563eb',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
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
        
        function exportStatistics() {
            // Logic for exporting statistics would go here
            // For now, just show an alert
            alert('Export functionality will be implemented soon.');
        }
    </script>
</x-app-layout> 