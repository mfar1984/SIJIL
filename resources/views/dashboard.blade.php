<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
    </x-slot>

    <x-slot name="title">Analytics Dashboard</x-slot>
    
    <x-slot name="styles">
        <style>
            .chart-container {
                background-color: white;
                border-radius: 0.5rem;
                padding: 1rem;
                height: 400px; 
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
    </x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">dashboard</span>
                        <h1 class="text-xl font-bold text-gray-800">Analytics Dashboard</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Comprehensive analytics and statistics</p>
                </div>
                
                <!-- Filter Form -->
                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-3">
                    <div>
                        <select name="period" id="period" class="text-xs rounded-md border-gray-300 shadow-sm">
                            <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $period == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_3_months" {{ $period == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="last_6_months" {{ $period == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="this_year" {{ $period == 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateContainer" class="flex items-center space-x-2 {{ $period == 'custom' ? '' : 'hidden' }}">
                        <div class="flex items-center">
                            <label for="start_date" class="text-xs mr-1">From:</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="text-xs rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex items-center">
                            <label for="end_date" class="text-xs mr-1">To:</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="text-xs rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow-sm text-xs">
                        <span class="material-icons text-xs mr-1">filter_alt</span>
                        Apply Filter
                    </button>
                </form>
            </div>
            </div>

        <div class="p-6">
            <!-- Summary Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Total Events</p>
                            <h3 class="text-2xl font-semibold mt-1">{{ number_format($totalEvents) }}</h3>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <span class="material-icons text-blue-600">event</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium">Total Participants</p>
                            <h3 class="text-2xl font-semibold mt-1">{{ number_format($totalParticipants) }}</h3>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <span class="material-icons text-green-600">groups</span>
                        </div>
            </div>
            </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Certificates Issued</p>
                            <h3 class="text-2xl font-semibold mt-1">{{ number_format($totalCertificates) }}</h3>
                        </div>
                        <div class="p-3 bg-amber-100 rounded-full">
                            <span class="material-icons text-amber-600">card_membership</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium">Total Attendance</p>
                            <h3 class="text-2xl font-semibold mt-1">{{ number_format($totalAttendance) }}</h3>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <span class="material-icons text-purple-600">how_to_reg</span>
                        </div>
            </div>
            </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Total Campaigns</p>
                            <h3 class="text-2xl font-semibold mt-1">{{ number_format($activeCampaigns) }}</h3>
                        </div>
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <span class="material-icons text-indigo-600">campaign</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section 1: Time Series -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Monthly Events Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                            <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-blue-600 mr-2">timeline</span>
                                Events Over Time
                            </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($monthlyEvents))
                                <canvas id="eventsChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">event_busy</span>
                                    <p>No event data available for the selected period</p>
                                </div>
                            @endif
                        </div>
                        @if(!empty($monthlyEvents))
                            <div class="mt-3 p-2 bg-gray-50 rounded-md text-xs text-gray-500">
                                <div class="flex justify-between">
                                    <span>Peak: {{ array_search(max($monthlyEvents), $monthlyEvents) }}</span>
                                    <span>Average: {{ count($monthlyEvents) > 0 ? round(array_sum(array_values($monthlyEvents)) / count($monthlyEvents), 1) : 0 }}</span>
                                </div>
                                </div>
                        @endif
                    </div>
                </div>

                <!-- Monthly Participants Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-green-600 mr-2">people</span>
                            Participants Over Time
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($monthlyParticipants))
                                <canvas id="participantsChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">groups_off</span>
                                    <p>No participant data available for the selected period</p>
                                    </div>
                            @endif
                                    </div>
                                    </div>
                </div>
        </div>
        
            <!-- Charts Section 2: Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gender Distribution Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-purple-600 mr-2">pie_chart</span>
                            Participant Gender Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($genderDistribution))
                                <canvas id="genderChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">pie_chart_off</span>
                                    <p>No gender distribution data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Event Status Distribution Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-amber-600 mr-2">pie_chart</span>
                            Event Status Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($eventStatusDistribution))
                                <canvas id="statusChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">pie_chart_off</span>
                                    <p>No event status data available</p>
                            </div>
                            @endif
                        </div>
                            </div>
                            </div>
                        </div>
            
            <!-- Charts Section 3: Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Campaign Performance Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-indigo-600 mr-2">campaign</span>
                            Campaign Performance
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($campaignPerformance) && $campaignPerformance->count() > 0)
                                <canvas id="campaignChart"></canvas>
                            @else
                                <div class="no-data-message">
                                <span class="material-icons">campaign</span>
                                    <p>No campaign performance data available</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Rate Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-green-600 mr-2">how_to_reg</span>
                            Attendance Rate by Event
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($attendanceRateByEvent) && count($attendanceRateByEvent) > 0)
                                <canvas id="attendanceChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">how_to_reg</span>
                                    <p>No attendance rate data available</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

                <!-- Cumulative Growth Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                            <h3 class="font-medium text-gray-700 flex items-center">
                        <span class="material-icons text-blue-600 mr-2">show_chart</span>
                                Cumulative Event Growth
                            </h3>
                    </div>
                    <div class="p-4">
                    <div class="chart-container">
                            @if(!empty($eventCumulativeGrowth))
                            <canvas id="growthChart"></canvas>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">show_chart</span>
                                    <p>No cumulative growth data available</p>
                                </div>
                            @endif
                        </div>
                    @if(!empty($eventCumulativeGrowth))
                        <div class="mt-3 p-2 bg-gray-50 rounded-md text-xs text-gray-500">
                            <div class="flex justify-between">
                                        @php
                                            $firstValue = reset($eventCumulativeGrowth) ?: 0;
                                            $lastValue = end($eventCumulativeGrowth) ?: 0;
                                            $growthRate = $firstValue > 0 ? round(($lastValue - $firstValue) / $firstValue * 100, 1) : 0;
                                        @endphp
                                <span>Growth rate: {{ $growthRate }}%</span>
                                <span>Time range: {{ count($eventCumulativeGrowth) }} periods</span>
                                </div>
                                </div>
                    @endif
                            </div>
                        </div>
                    </div>
                </div>
    
    <!-- Script for Chart.js 2.9.4 that has been proven to work -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Custom date range toggle
            const periodSelect = document.getElementById('period');
            const customDateContainer = document.getElementById('customDateContainer');
            
            if (periodSelect && customDateContainer) {
                periodSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customDateContainer.classList.remove('hidden');
                    } else {
                        customDateContainer.classList.add('hidden');
                    }
                });
            }
            
            // Initialize all charts
            console.log('DOM loaded, initializing charts with Chart.js 2.9.4');
            
            // Helper function to create gradient background
            function createGradient(ctx, startColor, endColor) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, startColor);
                gradient.addColorStop(1, endColor);
                return gradient;
            }
            
            // Events Chart
            @if(!empty($monthlyEvents))
            var eventsCtx = document.getElementById('eventsChart').getContext('2d');
            
            // Create gradient for events chart
            var eventsGradient = createGradient(eventsCtx, 'rgba(59, 130, 246, 0.6)', 'rgba(59, 130, 246, 0.1)');
            
            var eventsChart = new Chart(eventsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($monthlyEvents)) !!},
                    datasets: [{
                        label: 'Events',
                        data: {!! json_encode(array_values($monthlyEvents)) !!},
                        backgroundColor: eventsGradient,
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: '#6b7280',
                                padding: 15,
                                stepSize: 0.2 // Menambah jarak antara label nombor
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6b7280'
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        labels: {
                            fontColor: '#6b7280'
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFontColor: 'white',
                        bodyFontColor: 'white',
                        caretSize: 5,
                        cornerRadius: 4,
                        xPadding: 10,
                        yPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel;
                            }
                        }
                    }
                }
            });
            console.log('Events chart initialized');
            @endif
            
            // Participants Chart
            @if(!empty($monthlyParticipants))
            var participantsCtx = document.getElementById('participantsChart').getContext('2d');
            
            // Create gradient for participants chart
            var participantsGradient = createGradient(participantsCtx, 'rgba(16, 185, 129, 0.6)', 'rgba(16, 185, 129, 0.1)');
            
            var participantsChart = new Chart(participantsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($monthlyParticipants)) !!},
                    datasets: [{
                        label: 'Participants',
                        data: {!! json_encode(array_values($monthlyParticipants)) !!},
                        backgroundColor: participantsGradient,
                        borderColor: '#10b981',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: '#6b7280',
                                padding: 15,
                                stepSize: 4  // Menambah jarak antara label nombor
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6b7280'
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        labels: {
                            fontColor: '#6b7280'
                        }
                    }
                }
            });
            console.log('Participants chart initialized');
            @endif
            
            // Gender Distribution Chart
            @if(!empty($genderDistribution))
            var genderCtx = document.getElementById('genderChart').getContext('2d');
            var genderChart = new Chart(genderCtx, {
                type: 'doughnut',  // Changed from pie to doughnut
                data: {
                    labels: {!! json_encode(array_keys($genderDistribution)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($genderDistribution)) !!},
                        backgroundColor: ['#4f46e5', '#ec4899', '#6b7280'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverBorderColor: '#ffffff',
                        hoverBorderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 65,  // Doughnut hole size
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            fontColor: '#6b7280',
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                }
            });
            console.log('Gender chart initialized');
            @endif
            
            // Event Status Chart
            @if(!empty($eventStatusDistribution))
            var statusCtx = document.getElementById('statusChart').getContext('2d');
            var statusChart = new Chart(statusCtx, {
                type: 'doughnut',  // Changed from pie to doughnut
                data: {
                    labels: {!! json_encode(array_keys($eventStatusDistribution)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($eventStatusDistribution)) !!},
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverBorderColor: '#ffffff',
                        hoverBorderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 65,  // Doughnut hole size
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            fontColor: '#6b7280',
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                }
            });
            console.log('Status chart initialized');
            @endif
            
            // Campaign Performance Chart
            @if(isset($campaignPerformance) && $campaignPerformance->count() > 0)
            var campaignCtx = document.getElementById('campaignChart').getContext('2d');
            
            // Create gradients for campaign chart
            var campaignGradient1 = createGradient(campaignCtx, 'rgba(79, 70, 229, 0.6)', 'rgba(79, 70, 229, 0.1)');
            var campaignGradient2 = createGradient(campaignCtx, 'rgba(245, 158, 11, 0.6)', 'rgba(245, 158, 11, 0.0)');
            
            var campaignChart = new Chart(campaignCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($campaignPerformance->pluck('name')->toArray()) !!},
                    datasets: [
                        {
                            label: 'Open Rate (%)',
                            data: {!! json_encode($campaignPerformance->pluck('open_rate')->toArray()) !!},
                            backgroundColor: campaignGradient1,
                            borderColor: '#4f46e5',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4f46e5',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true
                        },
                        {
                            label: 'Click Rate (%)',
                            data: {!! json_encode($campaignPerformance->pluck('click_rate')->toArray()) !!},
                            backgroundColor: campaignGradient2,
                            borderColor: '#f59e0b',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#f59e0b',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100,
                                fontColor: '#6b7280',
                                padding: 15,
                                stepSize: 25  // Menambah jarak antara label nombor
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6b7280',
                                maxRotation: 30,
                                minRotation: 0,
                                callback: function(value) {
                                    // Truncate long campaign names
                                    if (value.length > 15) {
                                        return value.substr(0, 12) + '...';
                                    }
                                    return value;
                                }
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            fontColor: '#6b7280',
                            boxWidth: 12,
                            padding: 20
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFontColor: 'white',
                        bodyFontColor: 'white',
                        caretSize: 5,
                        cornerRadius: 4,
                        xPadding: 10,
                        yPadding: 10,
                        callbacks: {
                            title: function(tooltipItem) {
                                // Show full campaign name in tooltip
                                return {!! json_encode($campaignPerformance->pluck('name')->toArray()) !!}[tooltipItem[0].index];
                            }
                        }
                    }
                }
            });
            console.log('Campaign chart initialized');
            @endif
            
            // Attendance Rate Chart
            @if(isset($attendanceRateByEvent) && count($attendanceRateByEvent) > 0)
            var attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            
            // Create gradient for attendance chart
            var attendanceGradient = createGradient(attendanceCtx, 'rgba(16, 185, 129, 0.6)', 'rgba(16, 185, 129, 0.1)');
            
            var attendanceChart = new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(collect($attendanceRateByEvent)->pluck('name')->toArray()) !!},
                    datasets: [{
                        label: 'Attendance Rate (%)',
                        data: {!! json_encode(collect($attendanceRateByEvent)->pluck('attendance_rate')->toArray()) !!},
                        backgroundColor: attendanceGradient,
                        borderColor: '#10b981',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100,
                                fontColor: '#6b7280',
                                padding: 15,
                                stepSize: 25 // Menambah jarak antara label nombor
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6b7280'
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        labels: {
                            fontColor: '#6b7280'
                        }
                    }
                }
            });
            console.log('Attendance chart initialized');
            @endif
            
            // Growth Chart - enhanced line chart
            @if(!empty($eventCumulativeGrowth))
            var growthCtx = document.getElementById('growthChart').getContext('2d');
            
            // Create gradient for the area under the line
            var growthGradient = growthCtx.createLinearGradient(0, 0, 0, 400);
            growthGradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
            growthGradient.addColorStop(0.8, 'rgba(59, 130, 246, 0.0)');
            
            var growthChart = new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($monthlyEvents)) !!},
                    datasets: [{
                        label: 'Cumulative Events',
                        data: {!! json_encode(array_values($eventCumulativeGrowth)) !!},
                        backgroundColor: growthGradient,
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: '#6b7280',
                                padding: 15,
                                stepSize: 0.5 // Menambah jarak antara label nombor
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6b7280'
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        labels: {
                            fontColor: '#6b7280'
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFontColor: 'white',
                        bodyFontColor: 'white',
                        caretSize: 5,
                        cornerRadius: 4,
                        xPadding: 10,
                        yPadding: 10
                    }
                }
            });
            console.log('Growth chart initialized');
            @endif
        });
    </script>
</x-app-layout>
