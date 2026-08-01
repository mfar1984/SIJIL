<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Event Statistics</span>
    </x-slot>

    <x-slot name="title">Event Statistics</x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @php
        $filters = ['search', 'date_filter', 'start_date', 'end_date', 'organizer', 'status_filter', 'sort'];
        $hasFilters = collect($filters)->contains(fn ($f) => request()->filled($f));
        $isCustom = request('date_filter') === 'custom';

        // Reused by every card so a missing comparison reads the same way each time.
        $changeBadge = function (?float $change) {
            if ($change === null) {
                return ['text-gray-400', 'remove', 'no earlier data'];
            }

            if ($change > 0) {
                return ['text-green-600', 'trending_up', '+' . $change . '% vs previous period'];
            }

            if ($change < 0) {
                return ['text-red-600', 'trending_down', $change . '% vs previous period'];
            }

            return ['text-gray-500', 'trending_flat', 'unchanged'];
        };
    @endphp

    <div class="space-y-3">
        <div class="bg-white rounded shadow-md border border-gray-300">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center">
                            <span class="material-icons-outlined mr-2 text-primary-DEFAULT">insights</span>
                            <h1 class="text-xl font-bold text-gray-800">Event Statistics</h1>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-8">
                            Events starting {{ $rangeLabel }}. Every figure below describes that set of events.
                        </p>
                    </div>

                    @can('event_statistics.export')
                        <form method="POST" action="{{ route('reports.statistics.export') }}">
                            @csrf
                            @foreach($filters as $carry)
                                <input type="hidden" name="{{ $carry }}" value="{{ request($carry) }}">
                            @endforeach
                            <button type="submit"
                                    class="h-9 px-3 rounded text-xs font-medium text-white bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 inline-flex items-center shadow-sm">
                                <span class="material-icons-outlined text-sm mr-1">file_download</span>
                                Export CSV
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="p-4">
                {{-- The range select no longer submits on change: doing so reloaded the
                     page the instant "Custom range" was picked, before the two date
                     inputs could be revealed, which made the custom range unusable. --}}
                <form method="GET" action="{{ route('reports.statistics') }}" class="space-y-2 mb-4"
                      x-data="{ custom: {{ $isCustom ? 'true' : 'false' }} }">
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search event name, location, description..."
                               class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                        <select name="date_filter" x-model="custom" x-init="$el.value = '{{ request('date_filter', 'last_year') }}'"
                                @change="custom = ($event.target.value === 'custom')"
                                class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="last_30" @selected(request('date_filter') === 'last_30')>Last 30 days</option>
                            <option value="last_90" @selected(request('date_filter') === 'last_90')>Last 90 days</option>
                            <option value="last_6_months" @selected(request('date_filter') === 'last_6_months')>Last 6 months</option>
                            <option value="last_year" @selected(request('date_filter', 'last_year') === 'last_year')>Last 12 months</option>
                            <option value="all" @selected(request('date_filter') === 'all')>All time</option>
                            <option value="custom" @selected($isCustom)>Custom range</option>
                        </select>

                        @if($organizers->isNotEmpty())
                            <select name="organizer"
                                    class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[12rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="">All organizers</option>
                                @foreach($organizers as $organizer)
                                    <option value="{{ $organizer->id }}" @selected(request('organizer') == $organizer->id)>{{ $organizer->name }}</option>
                                @endforeach
                            </select>
                        @endif

                        <select name="status_filter"
                                class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="">Any status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status_filter') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>

                        <select name="sort"
                                class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="participants" @selected($sort === 'participants')>Sort: most participants</option>
                            <option value="certificates" @selected($sort === 'certificates')>Sort: most certificates</option>
                            <option value="coverage" @selected($sort === 'coverage')>Sort: best coverage</option>
                            <option value="attendance" @selected($sort === 'attendance')>Sort: best attendance</option>
                            <option value="date" @selected($sort === 'date')>Sort: newest first</option>
                        </select>

                        <button type="submit"
                                class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                            <span class="material-icons-outlined text-xs mr-1">filter_alt</span>
                            Apply
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('reports.statistics') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2" x-show="custom" x-cloak>
                        <label class="text-xs text-gray-600">From</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <label class="text-xs text-gray-600">to</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <span class="text-[11px] text-gray-500">Press Apply to use this range.</span>
                    </div>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach([
                        ['Events', $totals['events'], 'event', $changes['events'] ?? null, null],
                        ['Participants', $totals['participants'], 'groups', $changes['participants'] ?? null, null],
                        ['Certificates', $totals['certificates'], 'card_membership', $changes['certificates'] ?? null, $totals['coverage_rate'] . '% of participants'],
                        ['Checked in', $totals['checked_in'], 'how_to_reg', null, $totals['attendance_rate'] . '% of participants'],
                    ] as [$label, $value, $icon, $change, $note])
                        @php [$colour, $arrow, $changeText] = $changeBadge($change); @endphp
                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">{{ $label }}</p>
                                <span class="material-icons-outlined text-gray-300 text-base">{{ $icon }}</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($value) }}</p>

                            @if($note)
                                <p class="text-[11px] text-gray-500 mt-1">{{ $note }}</p>
                            @else
                                <p class="text-[11px] {{ $colour }} mt-1 flex items-center">
                                    <span class="material-icons-outlined text-xs mr-0.5">{{ $arrow }}</span>
                                    {{ $changeText }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($totals['sessions'] > 0 && $totals['checked_in'] === 0)
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded mt-3 text-xs">
                        {{ $totals['sessions'] }} check-in {{ $totals['sessions'] === 1 ? 'session exists' : 'sessions exist' }}
                        for these events but no scan has been recorded yet, so the attendance figures are 0.
                    </div>
                @endif

                @if($previous)
                    <p class="text-[11px] text-gray-500 mt-3">
                        Compared against {{ $previous['range'] }}:
                        {{ number_format($previous['events']) }} events,
                        {{ number_format($previous['participants']) }} participants,
                        {{ number_format($previous['certificates']) }} certificates.
                    </p>
                @endif
            </div>
        </div>

        @if($totals['events'] === 0)
            <div class="bg-white rounded shadow-md border border-gray-300 p-10 text-center">
                <span class="material-icons-outlined text-4xl text-gray-300">query_stats</span>
                <p class="text-sm text-gray-600 mt-2">No events start in this range.</p>
                <p class="text-xs text-gray-500 mt-1">Widen the range or clear the filters to see more.</p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Registrations and certificates over time</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">By the month each was recorded.</p>
                    </div>
                    <div class="p-4">
                        @if($registrationSeries->isNotEmpty() || $certificateSeries->isNotEmpty())
                            <div style="position: relative; height: 260px;">
                                <canvas id="timelineChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">show_chart</span>
                                <p class="text-xs">No registrations recorded yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Participants by event</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Ten largest in this range.</p>
                    </div>
                    <div class="p-4">
                        @if($participantsByEvent->isNotEmpty())
                            <div style="position: relative; height: 260px;">
                                <canvas id="participantsChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">bar_chart</span>
                                <p class="text-xs">No participants yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Who registered</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        {{ number_format($demographics['total']) }} participants across these events.
                    </p>
                </div>

                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach([
                        ['Gender', 'genderChart', $demographics['gender']],
                        ['Race', 'raceChart', $demographics['race']],
                        ['Registration type', 'typeChart', $demographics['type']],
                    ] as [$title, $canvasId, $series])
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-3">{{ $title }}</p>
                            @if($series->count() > 0)
                                <div style="position: relative; height: 200px;">
                                    <canvas id="{{ $canvasId }}"></canvas>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-[200px] text-gray-400">
                                    <span class="material-icons-outlined text-3xl mb-1">pie_chart</span>
                                    <p class="text-xs">Nothing recorded</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($coverageByEvent->isNotEmpty())
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Certificate coverage</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Share of each event's participants who hold a certificate.
                        </p>
                    </div>
                    <div class="p-4 space-y-2.5">
                        @foreach($coverageByEvent as $row)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-700 truncate pr-2">{{ $row['label'] }}</span>
                                    <span class="text-gray-500 shrink-0">
                                        {{ number_format($row['issued']) }} of {{ number_format($row['registered']) }}
                                        ({{ $row['percent'] }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $row['percent'] >= 90 ? 'bg-green-600' : ($row['percent'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                         style="width: {{ min(100, $row['percent']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700">Events</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{-- This card used to be titled "Top Performing Events" while
                                 listing the current page ordered by start date. It is
                                 sorted by the metric chosen above. --}}
                            Sorted by {{ str_replace('_', ' ', $sort) }}.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left">Event</th>
                                @if($organizers->isNotEmpty())
                                    <th class="py-3 px-4 text-left">Organizer</th>
                                @endif
                                <th class="py-3 px-4 text-left">Starts</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-right">Participants</th>
                                <th class="py-3 px-4 text-right">Certificates</th>
                                <th class="py-3 px-4 text-left">Coverage</th>
                                <th class="py-3 px-4 text-left">Attendance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($events as $row)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-800">{{ $row['name'] }}</div>
                                        @if($row['location'])
                                            <div class="text-gray-500">{{ $row['location'] }}</div>
                                        @endif
                                    </td>
                                    @if($organizers->isNotEmpty())
                                        <td class="py-3 px-4">{{ $row['organizer'] }}</td>
                                    @endif
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        {{ $row['start_date'] ? $row['start_date']->format('d M Y') : '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-[11px] {{ $row['status'] === 'completed' ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700' }}">
                                            {{ ucfirst($row['status'] ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">{{ number_format($row['participants']) }}</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($row['certificates']) }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <div class="w-14 bg-gray-200 rounded-full h-1.5 shrink-0">
                                                <div class="bg-primary-DEFAULT h-1.5 rounded-full" style="width: {{ min(100, $row['coverage']) }}%"></div>
                                            </div>
                                            <span class="ml-2">{{ $row['coverage'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($row['sessions'] === 0)
                                            <span class="text-gray-400">No sessions</span>
                                        @else
                                            <div class="flex items-center">
                                                <div class="w-14 bg-gray-200 rounded-full h-1.5 shrink-0">
                                                    <div class="bg-teal-600 h-1.5 rounded-full" style="width: {{ min(100, $row['attendance']) }}%"></div>
                                                </div>
                                                <span class="ml-2">{{ $row['attendance'] }}%</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-gray-200">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        Showing {{ $events->count() }} of {{ number_format($events->total()) }} events
                    </div>
                    <div class="flex justify-end">
                        {{ $events->links('components.pagination-modern') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gridColour = 'rgba(0, 0, 0, 0.06)';

            // A fixed palette so a category keeps its colour between loads.
            const palette = [
                '#4f46e5', '#0d9488', '#f59e0b', '#ef4444', '#8b5cf6',
                '#06b6d4', '#65a30d', '#db2777', '#0284c7', '#ea580c',
                '#7c3aed', '#059669', '#c026d3', '#475569'
            ];

            function doughnut(canvasId, rows) {
                const canvas = document.getElementById(canvasId);

                if (!canvas || !rows.length) {
                    return;
                }

                let next = 0;
                const colours = rows.map(r => r.blank ? '#d1d5db' : palette[next++ % palette.length]);

                new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: rows.map(r => r.label),
                        datasets: [{
                            data: rows.map(r => r.count),
                            backgroundColor: colours,
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const row = rows[ctx.dataIndex];
                                        return ' ' + row.label + ': ' + row.count + ' (' + row.percent + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            @if($totals['events'] > 0)
            const timelineCanvas = document.getElementById('timelineChart');

            if (timelineCanvas) {
                // Series come through json encoding rather than being written into an
                // array literal by a Blade loop. The old page built them with a loop
                // that emitted trailing commas straight into the JavaScript.
                const registrations = @json($registrationSeries);
                const certificates = @json($certificateSeries);

                // One axis for both series, so the months line up.
                const labels = [...new Set([
                    ...registrations.map(r => r.label),
                    ...certificates.map(r => r.label)
                ])];

                const byLabel = (rows) => {
                    const map = Object.fromEntries(rows.map(r => [r.label, r.count]));
                    return labels.map(l => map[l] ?? 0);
                };

                new Chart(timelineCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Registrations',
                                data: byLabel(registrations),
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: labels.length > 24 ? 0 : 3
                            },
                            {
                                label: 'Certificates',
                                data: byLabel(certificates),
                                borderColor: '#0d9488',
                                backgroundColor: 'rgba(13, 148, 136, 0.12)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: labels.length > 24 ? 0 : 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }

            const participantsCanvas = document.getElementById('participantsChart');

            if (participantsCanvas) {
                const rows = @json($participantsByEvent);

                new Chart(participantsCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: rows.map(r => r.label),
                        datasets: [{
                            label: 'Participants',
                            data: rows.map(r => r.count),
                            backgroundColor: 'rgba(79, 70, 229, 0.75)',
                            borderRadius: 3,
                            maxBarThickness: 22
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function (value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 28 ? label.slice(0, 27) + '…' : label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            doughnut('genderChart', @json($demographics['gender']));
            doughnut('raceChart', @json($demographics['race']));
            doughnut('typeChart', @json($demographics['type']));
            @endif
        });
    </script>
</x-app-layout>
