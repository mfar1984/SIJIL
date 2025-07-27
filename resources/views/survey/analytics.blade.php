<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>{{ $survey->title }}</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Analytics</span>
    </x-slot>

    <x-slot name="title">Survey Analytics</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">insights</span>
                        <h1 class="text-xl font-bold text-gray-800">Analytics for: {{ $survey->title }}</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        {{ $survey->completed_responses_count }} responses collected
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('survey.responses', $survey) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">format_list_bulleted</span>
                        View All Responses
                    </a>
                    <a href="{{ route('survey.show', $survey) }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to Survey
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            @if($survey->completed_responses_count == 0)
                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-yellow-800 flex items-center">
                    <span class="material-icons mr-2">warning</span>
                    <div>
                        <p class="font-bold">No responses yet</p>
                        <p class="text-xs mt-1">This survey doesn't have any completed responses yet. Analytics will be available once responses are collected.</p>
                    </div>
                </div>
            @else
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 border border-blue-100 rounded p-4">
                        <div class="flex items-center">
                            <span class="material-icons text-blue-700 mr-2">leaderboard</span>
                            <h3 class="text-xs font-medium text-blue-800">Total Responses</h3>
                        </div>
                        <div class="flex items-end mt-2">
                            <span class="text-3xl font-bold text-blue-700">{{ $survey->completed_responses_count }}</span>
                            @if($survey->responses()->where('completed', false)->count() > 0)
                                <span class="text-[10px] text-blue-600 ml-2 mb-1">(+ {{ $survey->responses()->where('completed', false)->count() }} incomplete)</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-100 rounded p-4">
                        <div class="flex items-center">
                            <span class="material-icons text-green-700 mr-2">percent</span>
                            <h3 class="text-xs font-medium text-green-800">Completion Rate</h3>
                        </div>
                        @php
                            $totalResponses = $survey->responses()->count();
                            $completionRate = $totalResponses > 0 ? round(($survey->completed_responses_count / $totalResponses) * 100) : 0;
                        @endphp
                        <div class="flex items-end mt-2">
                            <span class="text-3xl font-bold text-green-700">{{ $completionRate }}%</span>
                        </div>
                        <div class="mt-2 bg-white h-2 rounded-full border border-green-200">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-100 rounded p-4">
                        <div class="flex items-center">
                            <span class="material-icons text-purple-700 mr-2">timer</span>
                            <h3 class="text-xs font-medium text-purple-800">Average Completion Time</h3>
                        </div>
                        @php
                            $completedResponses = $survey->responses()->where('completed', true)->get();
                            $totalTime = 0;
                            $countWithTime = 0;
                            
                            foreach ($completedResponses as $response) {
                                if ($response->time_taken) {
                                    $totalTime += $response->time_taken;
                                    $countWithTime++;
                                }
                            }
                            
                            $avgTime = $countWithTime > 0 ? round($totalTime / $countWithTime) : 0;
                        @endphp
                        <div class="flex items-end mt-2">
                            <span class="text-3xl font-bold text-purple-700">{{ $avgTime }}</span>
                            <span class="text-[10px] text-purple-600 ml-1 mb-1">minutes</span>
                        </div>
                    </div>
                </div>
                
                <!-- Responses Over Time -->
                <div class="border-b border-gray-200 pb-6">
                    <div class="flex items-center mb-4">
                        <span class="material-icons mr-2 text-primary-DEFAULT">timeline</span>
                        <h2 class="text-sm font-semibold text-gray-700">Responses Over Time</h2>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded p-4">
                        <canvas id="responsesChart" height="100"></canvas>
                    </div>
                </div>
                
                <!-- Question Analysis -->
                <div>
                    <div class="flex items-center mb-4">
                        <span class="material-icons mr-2 text-primary-DEFAULT">analytics</span>
                        <h2 class="text-sm font-semibold text-gray-700">Question Analysis</h2>
                    </div>
                    
                    @foreach($questions as $question)
                        <div class="bg-white border border-gray-200 rounded shadow-sm mb-6 overflow-hidden">
                            <div class="bg-gray-50 p-4 border-b border-gray-200">
                                <h4 class="font-medium text-xs">
                                    {{ $loop->iteration }}. {{ $question->question_text }}
                                    @if($question->required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </h4>
                                @if($question->description)
                                    <p class="text-[10px] text-gray-500 mt-1">{{ $question->description }}</p>
                                @endif
                                <div class="flex items-center text-[10px] text-gray-500 mt-2">
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">
                                        {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                    </span>
                                    @if(in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown', 'rating']))
                                        <span class="ml-2">{{ $question->statistics['total'] ?? 0 }} responses</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="p-4">
                                @switch($question->question_type)
                                    @case('multiple_choice')
                                    @case('dropdown')
                                        @if(isset($question->statistics) && !empty($question->statistics['data']))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <canvas id="chart-{{ $question->id }}" height="200"></canvas>
                                                </div>
                                                <div>
                                                    <div class="bg-gray-50 border border-gray-200 rounded overflow-hidden">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead>
                                                                <tr class="bg-gray-50">
                                                                    <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Option</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach($question->statistics['data'] as $item)
                                                                    <tr>
                                                                        <td class="py-2 px-4 text-xs">{{ $item['label'] }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['count'] }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['percentage'] }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-xs">No data available for this question.</p>
                                        @endif
                                        @break
                                        
                                    @case('checkbox')
                                        @if(isset($question->statistics) && !empty($question->statistics['data']))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <canvas id="chart-{{ $question->id }}" height="200"></canvas>
                                                </div>
                                                <div>
                                                    <div class="bg-gray-50 border border-gray-200 rounded overflow-hidden">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead>
                                                                <tr class="bg-gray-50">
                                                                    <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Option</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach($question->statistics['data'] as $item)
                                                                    <tr>
                                                                        <td class="py-2 px-4 text-xs">{{ $item['label'] }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['count'] }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['percentage'] }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-xs">No data available for this question.</p>
                                        @endif
                                        @break
                                        
                                    @case('rating')
                                        @if(isset($question->statistics) && !empty($question->statistics['data']))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <canvas id="chart-{{ $question->id }}" height="200"></canvas>
                                                </div>
                                                <div>
                                                    @php
                                                        $totalRating = 0;
                                                        $responseCount = 0;
                                                        foreach($question->statistics['data'] as $item) {
                                                            $totalRating += ($item['label'] * $item['count']);
                                                            $responseCount += $item['count'];
                                                        }
                                                        $averageRating = $responseCount > 0 ? round($totalRating / $responseCount, 2) : 0;
                                                    @endphp
                                                    
                                                    <div class="bg-gray-50 border border-gray-200 p-4 rounded mb-4">
                                                        <div class="text-xs text-gray-700 mb-2">Average Rating</div>
                                                        <div class="flex items-center">
                                                            <span class="text-3xl font-bold text-gray-800">{{ $averageRating }}</span>
                                                            <div class="ml-3">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    @if($i <= round($averageRating))
                                                                        <span class="material-icons text-yellow-500">star</span>
                                                                    @else
                                                                        <span class="material-icons text-gray-300">star_outline</span>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                        </div>
                                                        <div class="text-[10px] text-gray-500 mt-1">Based on {{ $responseCount }} responses</div>
                                                    </div>
                                                    
                                                    <div class="bg-gray-50 border border-gray-200 rounded overflow-hidden">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead>
                                                                <tr class="bg-gray-50">
                                                                    <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                                                    <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach($question->statistics['data'] as $item)
                                                                    <tr>
                                                                        <td class="py-2 px-4 text-xs">{{ $item['label'] }} star{{ $item['label'] > 1 ? 's' : '' }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['count'] }}</td>
                                                                        <td class="py-2 px-4 text-xs text-right">{{ $item['percentage'] }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-xs">No data available for this question.</p>
                                        @endif
                                        @break
                                        
                                    @case('text')
                                    @case('textarea')
                                        @if(isset($question->statistics) && !empty($question->statistics['data']))
                                            <div class="space-y-3">
                                                <div class="text-xs text-gray-700 mb-1">{{ $question->statistics['total'] ?? 0 }} responses</div>
                                                
                                                @if($question->statistics['total'] > 0)
                                                    <div class="max-h-60 overflow-y-auto border border-gray-200 rounded">
                                                        @foreach($question->statistics['data'] as $answer)
                                                            <div class="bg-gray-50 p-3 border-b border-gray-200">
                                                                <p class="text-xs">{{ $answer }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-xs">No data available for this question.</p>
                                        @endif
                                        @break
                                        
                                    @case('date')
                                        @if(isset($question->statistics) && !empty($question->statistics['data']))
                                            <div class="space-y-3">
                                                <div class="text-xs text-gray-700 mb-1">{{ $question->statistics['total'] ?? 0 }} responses</div>
                                                
                                                @php
                                                    $dateCount = [];
                                                    foreach($question->statistics['data'] as $date) {
                                                        if (!isset($dateCount[$date])) {
                                                            $dateCount[$date] = 0;
                                                        }
                                                        $dateCount[$date]++;
                                                    }
                                                    arsort($dateCount);
                                                @endphp
                                                
                                                <div class="bg-gray-50 border border-gray-200 rounded overflow-hidden">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead>
                                                            <tr class="bg-gray-50">
                                                                <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                                <th class="py-2 px-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @foreach($dateCount as $date => $count)
                                                                <tr>
                                                                    <td class="py-2 px-4 text-xs">{{ $date }}</td>
                                                                    <td class="py-2 px-4 text-xs text-right">{{ $count }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-xs">No data available for this question.</p>
                                        @endif
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    @if($survey->completed_responses_count > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Set default chart colors
                const colorPalette = [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(201, 203, 207, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ];
                
                // Responses over time chart
                const responseData = @json($responsesByDate);
                const dates = responseData.map(item => item.date);
                const counts = responseData.map(item => item.count);
                
                new Chart(document.getElementById('responsesChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Responses',
                            data: counts,
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1,
                                    precision: 0,
                                    padding: 10
                                },
                                gridLines: {
                                    display: false
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
                
                // Question charts
                @foreach($questions as $question)
                    @if(in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown', 'rating']) && isset($question->statistics) && !empty($question->statistics['data']))
                        @php
                            $chartData = $question->statistics['data'];
                            $chartType = $question->question_type === 'rating' ? 'bar' : 'pie';
                        @endphp
                        
                        new Chart(document.getElementById('chart-{{ $question->id }}').getContext('2d'), {
                            type: '{{ $chartType }}',
                            data: {
                                labels: @json(collect($chartData)->pluck('label')),
                                datasets: [{
                                    data: @json(collect($chartData)->pluck('count')),
                                    backgroundColor: colorPalette.slice(0, {{ count($chartData) }}),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                legend: {
                                    position: '{{ $chartType === "pie" ? "right" : "top" }}',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15,
                                        fontSize: 10
                                    }
                                },
                                scales: {
                                    @if($chartType === 'bar')
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                            padding: 10,
                                            fontSize: 10
                                        },
                                        gridLines: {
                                            display: false
                                        }
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            fontSize: 10
                                        },
                                        gridLines: {
                                            display: false
                                        }
                                    }]
                                    @endif
                                },
                                tooltips: {
                                    titleFontSize: 12,
                                    bodyFontSize: 11,
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            if ('{{ $chartType }}' === 'pie') {
                                                const label = data.labels[tooltipItem.index];
                                                const value = data.datasets[0].data[tooltipItem.index];
                                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${value} (${percentage}%)`;
                                            } else {
                                                return tooltipItem.yLabel;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    @endif
                @endforeach
            });
        </script>
    @endif
</x-app-layout>
