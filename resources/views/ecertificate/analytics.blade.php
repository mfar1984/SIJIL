<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Analytics</span>
    </x-slot>

    <x-slot name="title">PWA Analytics</x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Handle custom date range visibility
        document.addEventListener('DOMContentLoaded', function() {
            const dateRangeSelect = document.getElementById('dateRange');
            const customDateContainer = document.getElementById('customDateContainer');
            
            if (dateRangeSelect && customDateContainer) {
                dateRangeSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customDateContainer.classList.remove('hidden');
                    } else {
                        customDateContainer.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <style>
        .chart-container {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            height: 300px; 
            position: relative;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        
        .no-data-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: #6b7280;
        }
        
        .no-data-message .material-icons {
            font-size: 48px;
            margin-bottom: 0.5rem;
            color: #d1d5db;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-indigo-500">analytics</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Analytics</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">View PWA usage statistics and user engagement metrics</p>
                </div>
                <div class="flex gap-2">
                    <select class="appearance-none px-3 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                        <option>Last 3 Months</option>
                        <option>Last Year</option>
                    </select>
                    <a href="{{ route('pwa.analytics.export') }}" class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium flex items-center">
                        <span class="material-icons text-xs mr-1">download</span>
                        Export
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            @if(!isset($tablesExist) || $tablesExist)
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total PWA Users -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs opacity-90">Total PWA Users</p>
                                <p class="text-2xl font-bold">{{ number_format($totalParticipants ?? 0) }}</p>
                                <p class="text-xs opacity-90 mt-1">Registered participants</p>
                            </div>
                            <span class="material-icons text-3xl opacity-80">people</span>
                        </div>
                    </div>

                    <!-- Total Events -->
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs opacity-90">Total Events</p>
                                <p class="text-2xl font-bold">{{ number_format($totalEvents ?? 0) }}</p>
                                <p class="text-xs opacity-90 mt-1">Events with PWA access</p>
                            </div>
                            <span class="material-icons text-3xl opacity-80">event</span>
                        </div>
                    </div>

                    <!-- Total Attendance -->
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs opacity-90">Total Attendance</p>
                                <p class="text-2xl font-bold">{{ number_format($totalAttendance ?? 0) }}</p>
                                <p class="text-xs opacity-90 mt-1">Check-ins recorded</p>
                            </div>
                            <span class="material-icons text-3xl opacity-80">qr_code_scanner</span>
                        </div>
                    </div>

                    <!-- Total Certificates -->
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs opacity-90">Total Certificates</p>
                                <p class="text-2xl font-bold">{{ number_format($totalCertificates ?? 0) }}</p>
                                <p class="text-xs opacity-90 mt-1">Certificates issued</p>
                            </div>
                            <span class="material-icons text-3xl opacity-80">download</span>
                        </div>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="mb-6">
                    <form method="GET" class="flex gap-2 items-center justify-between">
                        <div class="flex gap-2 items-center">
                            @if(isset($events) && $events->count() > 0)
                            <select name="event_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ $selectedEventId == $event->id ? 'selected' : '' }}>
                                        {{ $event->name }}
                                    </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        
                        <div class="flex gap-2 items-center">
                            <select name="date_range" id="dateRange" class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="60" {{ $dateRange == '60' ? 'selected' : '' }}>Last 60 Days</option>
                                <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last 1 Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                            
                            <div id="customDateContainer" class="flex gap-2 items-center {{ $dateRange == 'custom' ? '' : 'hidden' }}">
                                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <span class="text-gray-500">to</span>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                            </div>
                            
                            <button type="submit" class="bg-indigo-500 text-white px-3 py-2 rounded text-sm font-medium flex items-center">
                                <span class="material-icons text-xs mr-1">filter_alt</span>
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Monthly Registration Chart -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly PWA Registrations</h3>
                        <div class="h-64">
                            <canvas id="monthlyRegistrationsChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Performing Events Chart -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Performing Events</h3>
                        <div class="h-64">
                            <canvas id="topEventsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Activity Trends -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-purple-600 mr-2">trending_up</span>
                            Activity Trends ({{ $dateRange == 'custom' ? $startDate . ' to ' . $endDate : 'Last ' . $dateRange . ' Days' }})
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($activityTrends) && $activityTrends->count() > 0)
                                <canvas id="activityTrendsChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">trending_up</span>
                                    <p>No activity trends available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Setup Required Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <div class="flex items-center">
                        <span class="material-icons text-yellow-600 mr-3">warning</span>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800">Database Setup Required</h3>
                            <p class="text-xs text-yellow-700 mt-1">PWA tables need to be created. Please run the migrations to set up the database.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Monthly Registrations Line Chart
        @if(isset($monthlyStats) && $monthlyStats->count() > 0)
        const monthlyCtx = document.getElementById('monthlyRegistrationsChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyStats->pluck('month')) !!},
                datasets: [{
                    label: 'PWA Registrations',
                    data: {!! json_encode($monthlyStats->pluck('count')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        @endif

        // Top Events Bar Chart
        @if(isset($topEvents) && $topEvents->count() > 0)
        const eventsCtx = document.getElementById('topEventsChart').getContext('2d');
        new Chart(eventsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topEvents->pluck('name')) !!},
                datasets: [{
                    label: 'Participants',
                    data: {!! json_encode($topEvents->pluck('participant_count')) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        @endif

        // Activity Trends Chart (2 Lines)
        @if(isset($activityTrends) && $activityTrends->count() > 0)
        const trendsCtx = document.getElementById('activityTrendsChart').getContext('2d');
        
        const trendsData = {!! json_encode($activityTrends) !!};
        const labels = trendsData.map(item => item.day);
        const registrationData = trendsData.map(item => item.registrations);
        const checkinData = trendsData.map(item => item.checkins);
        
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Registrations',
                        data: registrationData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Check-ins',
                        data: checkinData,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });
        @endif
    </script>
</x-app-layout> 