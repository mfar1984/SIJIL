<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
    </x-slot>

    <x-slot name="title">Analytics Dashboard</x-slot>
    
    <x-slot name="styles">
        <!-- ApexCharts CDN -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
        
        <!-- CountUp.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/countup.js@2.8.0/dist/countUp.umd.min.js"></script>
        
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
            
            /* Animation Styles */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            @keyframes shimmer {
                0% {
                    background-position: -1000px 0;
                }
                100% {
                    background-position: 1000px 0;
                }
            }
            
            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
            }
            
            .animate-fade-in {
                animation: fadeIn 0.3s ease-out forwards;
            }
            
            /* Skeleton Loader Styles */
            .skeleton-loader {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 1000px 100%;
                animation: shimmer 1.5s infinite;
                border-radius: 0.5rem;
            }
            
            .skeleton-chart {
                width: 100%;
                height: 350px;
            }
            
            .skeleton-card {
                width: 100%;
                height: 80px;
            }
            
            /* Tooltip Styles */
            .tooltip-wrapper {
                position: relative;
                display: inline-flex;
            }
            
            .tooltip-content {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%) translateY(-4px);
                background-color: #1f2937;
                color: white;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 11px;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            
            .tooltip-content::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #1f2937;
            }
            
            /* Stagger Animation Delays */
            .stagger-1 { animation-delay: 0ms; }
            .stagger-2 { animation-delay: 100ms; }
            .stagger-3 { animation-delay: 200ms; }
            .stagger-4 { animation-delay: 300ms; }
            .stagger-5 { animation-delay: 400ms; }
            
            /* Chart Loading State */
            .chart-loading {
                opacity: 0;
            }
            
            .chart-loaded {
                opacity: 1;
                transition: opacity 0.3s ease-in;
            }
        </style>
    </x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">dashboard</span>
                        <h1 class="text-xl font-bold text-gray-800">Analytics Dashboard</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Comprehensive analytics and statistics</p>
                </div>
                
                <!-- Filter Form -->
                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-3">
                    <div>
                        <select name="period" id="period" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[150px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
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
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 h-[36px] rounded shadow-sm text-xs font-medium flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">filter_alt</span>
                        Apply Filter
                    </button>
                </form>
            </div>
            </div>

        <div class="p-6">
            <!-- Summary Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 animate-fade-in-up stagger-1">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Total Events</p>
                            <h3 class="text-2xl font-semibold mt-1" id="totalEventsCount">{{ number_format($totalEvents) }}</h3>
                            @if(isset($previousPeriodEvents))
                            <div class="mt-1 text-xs">
                                @php
                                    $eventsChange = $previousPeriodEvents > 0 ? (($totalEvents - $previousPeriodEvents) / $previousPeriodEvents) * 100 : 0;
                                    $eventsDirection = $eventsChange > 0 ? 'up' : ($eventsChange < 0 ? 'down' : 'neutral');
                                @endphp
                                <span class="flex items-center {{ $eventsDirection == 'up' ? 'text-green-600' : ($eventsDirection == 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($eventsDirection == 'up')
                                        <span class="material-icons text-xs">arrow_upward</span>
                                    @elseif($eventsDirection == 'down')
                                        <span class="material-icons text-xs">arrow_downward</span>
                                    @else
                                        <span class="material-icons text-xs">remove</span>
                                    @endif
                                    {{ number_format(abs($eventsChange), 1) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="material-icons-outlined text-blue-600 text-2xl">event</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 animate-fade-in-up stagger-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium">Total Participants</p>
                            <h3 class="text-2xl font-semibold mt-1" id="totalParticipantsCount">{{ number_format($totalParticipants) }}</h3>
                            @if(isset($previousPeriodParticipants))
                            <div class="mt-1 text-xs">
                                @php
                                    $participantsChange = $previousPeriodParticipants > 0 ? (($totalParticipants - $previousPeriodParticipants) / $previousPeriodParticipants) * 100 : 0;
                                    $participantsDirection = $participantsChange > 0 ? 'up' : ($participantsChange < 0 ? 'down' : 'neutral');
                                @endphp
                                <span class="flex items-center {{ $participantsDirection == 'up' ? 'text-green-600' : ($participantsDirection == 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($participantsDirection == 'up')
                                        <span class="material-icons text-xs">arrow_upward</span>
                                    @elseif($participantsDirection == 'down')
                                        <span class="material-icons text-xs">arrow_downward</span>
                                    @else
                                        <span class="material-icons text-xs">remove</span>
                                    @endif
                                    {{ number_format(abs($participantsChange), 1) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="material-icons-outlined text-green-600 text-2xl">groups</span>
                        </div>
            </div>
            </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 animate-fade-in-up stagger-3">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Certificates Issued</p>
                            <h3 class="text-2xl font-semibold mt-1" id="totalCertificatesCount">{{ number_format($totalCertificates) }}</h3>
                            @if(isset($previousPeriodCertificates))
                            <div class="mt-1 text-xs">
                                @php
                                    $certificatesChange = $previousPeriodCertificates > 0 ? (($totalCertificates - $previousPeriodCertificates) / $previousPeriodCertificates) * 100 : 0;
                                    $certificatesDirection = $certificatesChange > 0 ? 'up' : ($certificatesChange < 0 ? 'down' : 'neutral');
                                @endphp
                                <span class="flex items-center {{ $certificatesDirection == 'up' ? 'text-green-600' : ($certificatesDirection == 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($certificatesDirection == 'up')
                                        <span class="material-icons text-xs">arrow_upward</span>
                                    @elseif($certificatesDirection == 'down')
                                        <span class="material-icons text-xs">arrow_downward</span>
                                    @else
                                        <span class="material-icons text-xs">remove</span>
                                    @endif
                                    {{ number_format(abs($certificatesChange), 1) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                            <span class="material-icons-outlined text-amber-600 text-2xl">card_membership</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 animate-fade-in-up stagger-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium">Total Attendance</p>
                            <h3 class="text-2xl font-semibold mt-1" id="totalAttendanceCount">{{ number_format($totalAttendance) }}</h3>
                            @if(isset($previousPeriodAttendance))
                            <div class="mt-1 text-xs">
                                @php
                                    $attendanceChange = $previousPeriodAttendance > 0 ? (($totalAttendance - $previousPeriodAttendance) / $previousPeriodAttendance) * 100 : 0;
                                    $attendanceDirection = $attendanceChange > 0 ? 'up' : ($attendanceChange < 0 ? 'down' : 'neutral');
                                @endphp
                                <span class="flex items-center {{ $attendanceDirection == 'up' ? 'text-green-600' : ($attendanceDirection == 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($attendanceDirection == 'up')
                                        <span class="material-icons text-xs">arrow_upward</span>
                                    @elseif($attendanceDirection == 'down')
                                        <span class="material-icons text-xs">arrow_downward</span>
                                    @else
                                        <span class="material-icons text-xs">remove</span>
                                    @endif
                                    {{ number_format(abs($attendanceChange), 1) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="material-icons-outlined text-purple-600 text-2xl">how_to_reg</span>
                        </div>
            </div>
            </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 animate-fade-in-up stagger-5">
            <div class="flex items-center justify-between">
                <div>
                            <p class="text-gray-500 text-xs font-medium">Total Campaigns</p>
                            <h3 class="text-2xl font-semibold mt-1" id="totalCampaignsCount">{{ number_format($activeCampaigns) }}</h3>
                            @if(isset($previousPeriodCampaigns))
                            <div class="mt-1 text-xs">
                                @php
                                    $campaignsChange = $previousPeriodCampaigns > 0 ? (($activeCampaigns - $previousPeriodCampaigns) / $previousPeriodCampaigns) * 100 : 0;
                                    $campaignsDirection = $campaignsChange > 0 ? 'up' : ($campaignsChange < 0 ? 'down' : 'neutral');
                                @endphp
                                <span class="flex items-center {{ $campaignsDirection == 'up' ? 'text-green-600' : ($campaignsDirection == 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($campaignsDirection == 'up')
                                        <span class="material-icons text-xs">arrow_upward</span>
                                    @elseif($campaignsDirection == 'down')
                                        <span class="material-icons text-xs">arrow_downward</span>
                                    @else
                                        <span class="material-icons text-xs">remove</span>
                                    @endif
                                    {{ number_format(abs($campaignsChange), 1) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="material-icons-outlined text-indigo-600 text-2xl">campaign</span>
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
                            <span class="material-icons-outlined text-blue-600 mr-2">timeline</span>
                                Events Over Time
                            </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($monthlyEvents))
                                <div id="eventsChart"></div>
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
                            <span class="material-icons-outlined text-green-600 mr-2">people</span>
                            Participants Over Time
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($monthlyParticipants))
                                <div id="participantsChart"></div>
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
        
            <!-- Monthly Comparison Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-blue-600 mr-2">compare_arrows</span>
                        Monthly Comparison (This Year vs Last Year)
                    </h3>
                </div>
                <div class="p-4">
                    <div class="chart-container">
                        @if(!empty($monthlyComparison))
                            <div id="monthlyComparisonChart"></div>
                        @else
                            <div class="no-data-message">
                                <span class="material-icons">compare_arrows</span>
                                <p>No comparison data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charts Section 2: Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gender Distribution Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-purple-600 mr-2">pie_chart</span>
                            Participant Gender Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($genderDistribution))
                                <div id="genderChart"></div>
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
                            <span class="material-icons-outlined text-amber-600 mr-2">pie_chart</span>
                            Event Status Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($eventStatusDistribution))
                                <div id="statusChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">pie_chart_off</span>
                                    <p>No event status data available</p>
                            </div>
                            @endif
                        </div>
                            </div>
                            </div>
                
                <!-- Registration Type Distribution Chart (Donut) -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-green-600 mr-2">pie_chart</span>
                            Registration Type Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($registrationTypeBreakdown))
                                <div id="registrationTypeChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">pie_chart_off</span>
                                    <p>No registration type data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                        </div>
            
            
            <!-- Charts Section 3: Advanced Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Registration Heatmap Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-orange-600 mr-2">grid_on</span>
                            Registration Activity Heatmap
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($registrationHeatmap))
                                <div id="registrationHeatmapChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">grid_on</span>
                                    <p>No heatmap data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Participant Acquisition Funnel Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-purple-600 mr-2">filter_list</span>
                            Participant Acquisition Funnel
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($acquisitionFunnel))
                                <div id="acquisitionFunnelChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">filter_list</span>
                                    <p>No funnel data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Certificate Generation Rate Gauge -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-amber-600 mr-2">speed</span>
                            Certificate Generation Rate
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($certificateRate))
                                <div id="certificateRateGaugeChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">speed</span>
                                    <p>No certificate rate data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Event Performance Matrix (Bubble Chart) -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-blue-600 mr-2">bubble_chart</span>
                            Event Performance Matrix
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($eventPerformanceMatrix))
                                <div id="eventPerformanceMatrixChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">bubble_chart</span>
                                    <p>No performance matrix data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section 4: Rankings and Demographics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top 10 Events Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-blue-600 mr-2">bar_chart</span>
                            Top 10 Events by Participants
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($topEvents))
                                <div id="topEventsChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">bar_chart</span>
                                    <p>No top events data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Age Group Distribution Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-green-600 mr-2">bar_chart</span>
                            Age Group Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($ageGroupDistribution))
                                <div id="ageGroupChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">bar_chart</span>
                                    <p>No age group data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Event Category Distribution Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-purple-600 mr-2">category</span>
                            Event Category Distribution
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($eventCategoryDistribution))
                                <div id="eventCategoryChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">category</span>
                                    <p>No category data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Email Delivery Status Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-indigo-600 mr-2">email</span>
                            Email Delivery Status
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(!empty($emailDeliveryStatus))
                                <div id="emailDeliveryChart"></div>
                            @else
                                <div class="no-data-message">
                                    <span class="material-icons">email</span>
                                    <p>No email delivery data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Type by Month (Stacked Bar) -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-green-600 mr-2">stacked_bar_chart</span>
                        Registration Type by Month
                    </h3>
                </div>
                <div class="p-4">
                    <div class="chart-container">
                        @if(!empty($registrationTypeByMonth))
                            <div id="registrationTypeByMonthChart"></div>
                        @else
                            <div class="no-data-message">
                                <span class="material-icons">stacked_bar_chart</span>
                                <p>No registration type by month data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charts Section 3: Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Campaign Performance Chart -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-indigo-600 mr-2">campaign</span>
                            Campaign Performance
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($campaignPerformance) && $campaignPerformance->isNotEmpty())
                                <div id="campaignChart"></div>
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
                            <span class="material-icons-outlined text-green-600 mr-2">how_to_reg</span>
                            Attendance Rate by Event
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="chart-container">
                            @if(isset($attendanceRateByEvent) && $attendanceRateByEvent->isNotEmpty())
                                <div id="attendanceChart"></div>
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
                        <span class="material-icons-outlined text-blue-600 mr-2">show_chart</span>
                                Cumulative Event Growth
                            </h3>
                    </div>
                    <div class="p-4">
                    <div class="chart-container">
                            @if(!empty($eventCumulativeGrowth))
                            <div id="growthChart"></div>
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
            
            <!-- Data Tables Section -->
            @if(isset($eventPerformanceTable) || isset($monthlySummaryTable) || isset($demographicsTable))
            <div class="mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="material-icons-outlined text-gray-600 mr-2">table_chart</span>
                    Detailed Analytics Tables
                </h2>
                
                <!-- Event Performance Table -->
                @if(isset($eventPerformanceTable) && $eventPerformanceTable->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-blue-600 mr-2">event_note</span>
                            Event Performance Summary
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificates</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Simplified</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($eventPerformanceTable as $index => $event)
                                <tr class="hover:bg-gray-50 animate-fade-in" style="animation-delay: {{ $index * 50 }}ms;">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $event['name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($event['participants'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($event['certificates'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($event['attendance_rate'] ?? 0, 1) }}%</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($event['verified_count'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($event['simplified_count'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $event['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($event['status'] == 'ongoing' ? 'bg-blue-100 text-blue-800' : 
                                               'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($event['status'] ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Monthly Summary Table -->
                @if(isset($monthlySummaryTable) && !empty($monthlySummaryTable))
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-green-600 mr-2">calendar_month</span>
                            Monthly Summary
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificates</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Simplified</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $index = 0; @endphp
                                @foreach($monthlySummaryTable as $month => $data)
                                <tr class="hover:bg-gray-50 animate-fade-in" style="animation-delay: {{ $index * 50 }}ms;">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $month }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['events'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['participants'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['certificates'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['attendance_rate'] ?? 0, 1) }}%</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['verified'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($data['simplified'] ?? 0) }}</td>
                                </tr>
                                @php $index++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Participant Demographics Table -->
                @if(isset($demographicsTable) && !empty($demographicsTable))
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-purple-600 mr-2">people</span>
                            Participant Demographics
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($demographicsTable as $index => $demo)
                                <tr class="hover:bg-gray-50 animate-fade-in" style="animation-delay: {{ $index * 50 }}ms;">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $demo['age_group'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($demo['male'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($demo['female'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($demo['total'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center">
                                            <span class="mr-2">{{ number_format($demo['percentage'] ?? 0, 1) }}%</span>
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $demo['percentage'] ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    
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
            
            // Initialize CountUp.js for summary cards
            if (typeof countUp !== 'undefined') {
                // Total Events
                const eventsCount = new countUp.CountUp('totalEventsCount', {{ $totalEvents }}, {
                    duration: 2,
                    separator: ',',
                    decimal: '.'
                });
                if (!eventsCount.error) {
                    eventsCount.start();
                }
                
                // Total Participants
                const participantsCount = new countUp.CountUp('totalParticipantsCount', {{ $totalParticipants }}, {
                    duration: 2,
                    separator: ',',
                    decimal: '.'
                });
                if (!participantsCount.error) {
                    participantsCount.start();
                }
                
                // Total Certificates
                const certificatesCount = new countUp.CountUp('totalCertificatesCount', {{ $totalCertificates }}, {
                    duration: 2,
                    separator: ',',
                    decimal: '.'
                });
                if (!certificatesCount.error) {
                    certificatesCount.start();
                }
                
                // Total Attendance
                const attendanceCount = new countUp.CountUp('totalAttendanceCount', {{ $totalAttendance }}, {
                    duration: 2,
                    separator: ',',
                    decimal: '.'
                });
                if (!attendanceCount.error) {
                    attendanceCount.start();
                }
                
                // Total Campaigns
                const campaignsCount = new countUp.CountUp('totalCampaignsCount', {{ $activeCampaigns }}, {
                    duration: 2,
                    separator: ',',
                    decimal: '.'
                });
                if (!campaignsCount.error) {
                    campaignsCount.start();
                }
            }
            
            // Initialize all charts with ApexCharts
            
            // Events Chart (ApexCharts)
            @if(!empty($monthlyEvents))
            var eventsOptions = {
                series: [{
                    name: 'Events',
                    data: {!! json_encode(array_values($monthlyEvents)) !!}
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.6,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($monthlyEvents)) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' events';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            };
            
            var eventsChart = new ApexCharts(document.querySelector("#eventsChart"), eventsOptions);
            eventsChart.render();
            @endif
            
            // Participants Chart (ApexCharts)
            @if(!empty($monthlyParticipants))
            var participantsOptions = {
                series: [{
                    name: 'Participants',
                    data: {!! json_encode(array_values($monthlyParticipants)) !!}
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#10b981'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.6,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($monthlyParticipants)) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' participants';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            };
            
            var participantsChart = new ApexCharts(document.querySelector("#participantsChart"), participantsOptions);
            participantsChart.render();
            @endif
            
            // Monthly Comparison Chart (Grouped Bar)
            @if(!empty($monthlyComparison))
            var monthlyComparisonOptions = {
                series: [
                    {
                        name: '{{ $monthlyComparison["current_year_label"] ?? date("Y") }}',
                        data: {!! json_encode($monthlyComparison['current_year'] ?? []) !!}
                    },
                    {
                        name: '{{ $monthlyComparison["previous_year_label"] ?? (date("Y") - 1) }}',
                        data: {!! json_encode($monthlyComparison['previous_year'] ?? []) !!}
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6', '#9ca3af'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode($monthlyComparison['months'] ?? []) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' events';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var monthlyComparisonChart = new ApexCharts(document.querySelector("#monthlyComparisonChart"), monthlyComparisonOptions);
            monthlyComparisonChart.render();
            @endif
            
            // Gender Distribution Chart (ApexCharts)
            @if(!empty($genderDistribution))
            var genderOptions = {
                series: {!! json_encode(array_values($genderDistribution)) !!},
                chart: {
                    type: 'donut',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                labels: {!! json_encode(array_keys($genderDistribution)) !!},
                colors: ['#4f46e5', '#ec4899', '#6b7280'],
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                },
                stroke: {
                    width: 2,
                    colors: ['#ffffff']
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' participants';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var genderChart = new ApexCharts(document.querySelector("#genderChart"), genderOptions);
            genderChart.render();
            @endif
            
            // Event Status Chart (ApexCharts)
            @if(!empty($eventStatusDistribution))
            var statusOptions = {
                series: {!! json_encode(array_values($eventStatusDistribution)) !!},
                chart: {
                    type: 'donut',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                labels: {!! json_encode(array_keys($eventStatusDistribution)) !!},
                colors: ['#10b981', '#f59e0b'],
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                },
                stroke: {
                    width: 2,
                    colors: ['#ffffff']
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' events';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
            statusChart.render();
            @endif
            
            // Campaign Performance Chart (ApexCharts)
            @if(isset($campaignPerformance) && $campaignPerformance->isNotEmpty())
            var campaignOptions = {
                series: [
                    {
                        name: 'Open Rate (%)',
                        data: {!! json_encode($campaignPerformance->pluck('open_rate')->toArray()) !!}
                    },
                    {
                        name: 'Click Rate (%)',
                        data: {!! json_encode($campaignPerformance->pluck('click_rate')->toArray()) !!}
                    }
                ],
                chart: {
                    type: 'line',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#4f46e5', '#f59e0b'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: {!! json_encode($campaignPerformance->pluck('name')->toArray()) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        rotate: -45,
                        rotateAlways: true
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value) + '%';
                        }
                    }
                },
                grid: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + '%';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var campaignChart = new ApexCharts(document.querySelector("#campaignChart"), campaignOptions);
            campaignChart.render();
            @endif
            
            // Attendance Rate Chart (ApexCharts)
            @if(isset($attendanceRateByEvent) && $attendanceRateByEvent->isNotEmpty())
            var attendanceOptions = {
                series: [{
                    name: 'Attendance Rate (%)',
                    data: {!! json_encode(collect($attendanceRateByEvent)->pluck('attendance_rate')->toArray()) !!}
                }],
                chart: {
                    type: 'line',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#10b981'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: {!! json_encode(collect($attendanceRateByEvent)->pluck('name')->toArray()) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        rotate: -45,
                        rotateAlways: true
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value) + '%';
                        }
                    }
                },
                grid: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + '%';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
            attendanceChart.render();
            @endif
            
            // Growth Chart (ApexCharts - Area with trend line)
            @if(!empty($eventCumulativeGrowth))
            var growthOptions = {
                series: [{
                    name: 'Cumulative Events',
                    data: {!! json_encode(array_values($eventCumulativeGrowth)) !!}
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.0,
                        stops: [0, 80, 100]
                    }
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($eventCumulativeGrowth)) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' events';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var growthChart = new ApexCharts(document.querySelector("#growthChart"), growthOptions);
            growthChart.render();
            @endif
            
            // Registration Heatmap Chart
            @if(!empty($registrationHeatmap))
            var heatmapSeries = [];
            @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            @endphp
            @foreach($days as $day)
                @if(isset($registrationHeatmap[$day]))
                var dayData = [];
                @foreach($registrationHeatmap[$day] as $hour => $count)
                    dayData.push({
                        x: '{{ $hour }}',
                        y: {{ $count }}
                    });
                @endforeach
                heatmapSeries.push({
                    name: '{{ $day }}',
                    data: dayData
                });
                @endif
            @endforeach
            
            var heatmapOptions = {
                series: heatmapSeries,
                chart: {
                    type: 'heatmap',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#3b82f6'],
                plotOptions: {
                    heatmap: {
                        shadeIntensity: 0.5,
                        colorScale: {
                            ranges: [
                                {
                                    from: 0,
                                    to: 0,
                                    color: '#f3f4f6',
                                    name: 'No registrations'
                                }
                            ]
                        }
                    }
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' registrations';
                        }
                    }
                }
            };
            
            var registrationHeatmapChart = new ApexCharts(document.querySelector("#registrationHeatmapChart"), heatmapOptions);
            registrationHeatmapChart.render();
            @endif
            
            // Registration Type Distribution Chart (Donut)
            @if(!empty($registrationTypeBreakdown))
            var registrationTypeOptions = {
                series: [
                    {{ $registrationTypeBreakdown['verified'] ?? 0 }},
                    {{ $registrationTypeBreakdown['simplified'] ?? 0 }}
                ],
                chart: {
                    type: 'donut',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                labels: ['Verified', 'Simplified'],
                colors: ['#3b82f6', '#10b981'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                stroke: {
                    width: 2,
                    colors: ['#ffffff']
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' registrations';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var registrationTypeChart = new ApexCharts(document.querySelector("#registrationTypeChart"), registrationTypeOptions);
            registrationTypeChart.render();
            @endif
            
            // Registration Type by Month Chart (Stacked Bar)
            @if(!empty($registrationTypeByMonth))
            var registrationTypeByMonthOptions = {
                series: [
                    {
                        name: 'Verified',
                        data: {!! json_encode($registrationTypeByMonth['verified'] ?? []) !!}
                    },
                    {
                        name: 'Simplified',
                        data: {!! json_encode($registrationTypeByMonth['simplified'] ?? []) !!}
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6', '#10b981'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode($registrationTypeByMonth['months'] ?? []) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' registrations';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var registrationTypeByMonthChart = new ApexCharts(document.querySelector("#registrationTypeByMonthChart"), registrationTypeByMonthOptions);
            registrationTypeByMonthChart.render();
            @endif
            
            // Participant Acquisition Funnel Chart
            @if(!empty($acquisitionFunnel))
            var funnelOptions = {
                series: [
                    {
                        name: 'Participants',
                        data: {!! json_encode($acquisitionFunnel['counts'] ?? []) !!}
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#8b5cf6'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: '80%',
                        isFunnel: true
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opt) {
                        return opt.w.globals.labels[opt.dataPointIndex] + ': ' + val;
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                xaxis: {
                    categories: {!! json_encode($acquisitionFunnel['stages'] ?? []) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                            var percentage = {!! json_encode($acquisitionFunnel['percentages'] ?? []) !!}[dataPointIndex];
                            return value + ' participants (' + percentage + '%)';
                        }
                    }
                },
                legend: {
                    show: false
                }
            };
            
            var acquisitionFunnelChart = new ApexCharts(document.querySelector("#acquisitionFunnelChart"), funnelOptions);
            acquisitionFunnelChart.render();
            @endif
            
            // Certificate Generation Rate Gauge Chart
            @if(isset($certificateRate))
            var gaugeColor = '#ef4444'; // red
            @if($certificateRate > 80)
                gaugeColor = '#10b981'; // green
            @elseif($certificateRate >= 50)
                gaugeColor = '#f59e0b'; // amber
            @endif
            
            var gaugeOptions = {
                series: [{{ round($certificateRate, 1) }}],
                chart: {
                    type: 'radialBar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ['{{ $gaugeColor ?? "#ef4444" }}'],
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            margin: 0,
                            size: '70%',
                            background: '#fff',
                            position: 'front',
                            dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                            }
                        },
                        track: {
                            background: '#f3f4f6',
                            strokeWidth: '67%',
                            margin: 0
                        },
                        dataLabels: {
                            show: true,
                            name: {
                                offsetY: -10,
                                show: true,
                                color: '#6b7280',
                                fontSize: '14px'
                            },
                            value: {
                                formatter: function(val) {
                                    return val.toFixed(1) + '%';
                                },
                                color: '#111',
                                fontSize: '36px',
                                show: true
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: ['{{ $gaugeColor ?? "#ef4444" }}'],
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                stroke: {
                    lineCap: 'round'
                },
                labels: ['Certificate Rate']
            };
            
            var certificateRateGaugeChart = new ApexCharts(document.querySelector("#certificateRateGaugeChart"), gaugeOptions);
            certificateRateGaugeChart.render();
            @endif
            
            // Top 10 Events Chart (Horizontal Bar)
            @if(!empty($topEvents))
            var topEventsOptions = {
                series: [{
                    name: 'Participants',
                    data: {!! json_encode($topEvents->pluck('participant_count')->toArray()) !!}
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '70%',
                        distributed: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: {!! json_encode($topEvents->pluck('name')->toArray()) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' participants';
                        }
                    }
                }
            };
            
            var topEventsChart = new ApexCharts(document.querySelector("#topEventsChart"), topEventsOptions);
            topEventsChart.render();
            @endif
            
            // Event Performance Matrix Chart (Bubble)
            @if(!empty($eventPerformanceMatrix))
            var bubbleData = [];
            @foreach($eventPerformanceMatrix as $event)
                bubbleData.push({
                    x: {{ $event['attendance_rate'] ?? 0 }},
                    y: {{ $event['participant_count'] ?? 0 }},
                    z: {{ $event['certificate_count'] ?? 0 }},
                    name: '{{ addslashes($event['event_name'] ?? '') }}'
                });
            @endforeach
            
            var bubbleOptions = {
                series: [{
                    name: 'Events',
                    data: bubbleData
                }],
                chart: {
                    type: 'bubble',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#3b82f6'],
                dataLabels: {
                    enabled: false
                },
                fill: {
                    opacity: 0.8
                },
                xaxis: {
                    title: {
                        text: 'Attendance Rate (%)',
                        style: {
                            color: '#6b7280'
                        }
                    },
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Participant Count',
                        style: {
                            color: '#6b7280'
                        }
                    },
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    custom: function({ seriesIndex, dataPointIndex, w }) {
                        var data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                        return '<div class="p-2">' +
                            '<div class="font-semibold">' + data.name + '</div>' +
                            '<div>Attendance: ' + data.x + '%</div>' +
                            '<div>Participants: ' + data.y + '</div>' +
                            '<div>Certificates: ' + data.z + '</div>' +
                            '</div>';
                    }
                }
            };
            
            var eventPerformanceMatrixChart = new ApexCharts(document.querySelector("#eventPerformanceMatrixChart"), bubbleOptions);
            eventPerformanceMatrixChart.render();
            @endif
            
            // Age Group Distribution Chart
            @if(!empty($ageGroupDistribution))
            var ageGroupOptions = {
                series: [{
                    name: 'Participants',
                    data: {!! json_encode(array_values($ageGroupDistribution)) !!}
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#10b981'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($ageGroupDistribution)) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' participants';
                        }
                    }
                }
            };
            
            var ageGroupChart = new ApexCharts(document.querySelector("#ageGroupChart"), ageGroupOptions);
            ageGroupChart.render();
            @endif
            
            // Event Category Distribution Chart
            @if(!empty($eventCategoryDistribution))
            var categoryOptions = {
                series: [{
                    name: 'Events',
                    data: {!! json_encode(array_values($eventCategoryDistribution)) !!}
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#8b5cf6'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($eventCategoryDistribution)) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' events';
                        }
                    }
                }
            };
            
            var eventCategoryChart = new ApexCharts(document.querySelector("#eventCategoryChart"), categoryOptions);
            eventCategoryChart.render();
            @endif
            
            // Email Delivery Status Chart (Stacked Bar)
            @if(!empty($emailDeliveryStatus))
            var emailOptions = {
                series: [
                    {
                        name: 'Success',
                        data: {!! json_encode($emailDeliveryStatus['success'] ?? []) !!}
                    },
                    {
                        name: 'Failed',
                        data: {!! json_encode($emailDeliveryStatus['failed'] ?? []) !!}
                    },
                    {
                        name: 'Bounced',
                        data: {!! json_encode($emailDeliveryStatus['bounced'] ?? []) !!}
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#10b981', '#ef4444', '#f59e0b'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode($emailDeliveryStatus['campaigns'] ?? []) !!},
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280'
                        },
                        formatter: function(value) {
                            return Math.floor(value);
                        }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' emails';
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#6b7280'
                    }
                }
            };
            
            var emailDeliveryChart = new ApexCharts(document.querySelector("#emailDeliveryChart"), emailOptions);
            emailDeliveryChart.render();
            @endif
        });
    </script>
</x-app-layout>
