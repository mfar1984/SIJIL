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
            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="date_filter" class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                    <select id="date_filter" name="date_filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="last_30" {{ $dateFilter == 'last_30' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="last_90" {{ $dateFilter == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="last_6_months" {{ $dateFilter == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="last_year" {{ $dateFilter == 'last_year' ? 'selected' : '' }}>Last Year</option>
                        <option value="custom" {{ $dateFilter == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                
                <div>
                    <label for="event_type" class="block text-xs font-medium text-gray-700 mb-1">Event Type</label>
                    <select id="event_type" name="event_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Types</option>
                        <option value="Conference" {{ $eventType == 'Conference' ? 'selected' : '' }}>Conference</option>
                        <option value="Workshop" {{ $eventType == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                        <option value="Training" {{ $eventType == 'Training' ? 'selected' : '' }}>Training</option>
                        <option value="Seminar" {{ $eventType == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                        <option value="Gaming" {{ $eventType == 'Gaming' ? 'selected' : '' }}>Gaming</option>
                    </select>
                </div>
                
                <div>
                    <label for="organizer" class="block text-xs font-medium text-gray-700 mb-1">Organizer</label>
                    <select id="organizer" name="organizer" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Organizers</option>
                        @foreach($organizers as $organizer)
                            <option value="{{ $organizer->id }}" {{ $organizerId == $organizer->id ? 'selected' : '' }}>{{ $organizer->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="button" onclick="applyStatisticsFilters()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">filter_list</span>
                        Apply Filter
                    </button>
                    <button type="button" onclick="resetStatisticsFilters()" class="ml-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">refresh</span>
                        Reset
                    </button>
                </div>
            </div>
            
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
        
        function applyStatisticsFilters() {
            const dateFilter = document.getElementById('date_filter').value;
            const eventType = document.getElementById('event_type').value;
            const organizer = document.getElementById('organizer').value;
            
            let queryParams = [];
            
            if (dateFilter) {
                queryParams.push(`date_filter=${dateFilter}`);
            }
            
            if (eventType) {
                queryParams.push(`event_type=${encodeURIComponent(eventType)}`);
            }
            
            if (organizer) {
                queryParams.push(`organizer=${organizer}`);
            }
            
            const queryString = queryParams.length > 0 ? `?${queryParams.join('&')}` : '';
            window.location.href = `{{ route('reports.statistics') }}${queryString}`;
        }
        
        function resetStatisticsFilters() {
            window.location.href = "{{ route('reports.statistics') }}";
        }
        
        function exportStatistics() {
            // Logic for exporting statistics would go here
            // For now, just show an alert
            alert('Export functionality will be implemented soon.');
        }
    </script>
</x-app-layout> 