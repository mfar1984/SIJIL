<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
    </x-slot>

    <x-slot name="title">Dashboard</x-slot>

    {{-- Chart.js, matching the Event Statistics and PWA Analytics pages. The old
         dashboard loaded ApexCharts and CountUp instead, which made this the third
         charting library in one application. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @php
        $filters = ['date_filter', 'start_date', 'end_date', 'organizer', 'status_filter'];
        $hasFilters = collect($filters)->contains(fn ($f) => request()->filled($f));
        $isCustom = request('date_filter') === 'custom';

        // One phrasing for a missing comparison, used by every tile.
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
        {{-- Header, filter and headline figures --}}
        <div class="bg-white rounded shadow-md border border-gray-300">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center">
                            <span class="material-icons-outlined mr-2 text-primary-DEFAULT">space_dashboard</span>
                            <h1 class="text-xl font-bold text-gray-800">
                                {{-- Controlled by "Show welcome message on dashboard" on the
                                     Appearance tab, which previously had no effect anywhere. --}}
                                @if(\App\Support\Branding::showsWelcomeMessage())
                                    {{ \Illuminate\Support\Carbon::now()->hour < 12 ? 'Good morning' : (\Illuminate\Support\Carbon::now()->hour < 18 ? 'Good afternoon' : 'Good evening') }},
                                    {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}
                                @else
                                    Dashboard
                                @endif
                            </h1>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-8">
                            @if($isAdmin)
                                Events starting {{ $rangeLabel }}, across all organizers.
                            @else
                                Your events starting {{ $rangeLabel }}.
                            @endif
                            Every figure below describes that set of events.
                        </p>
                    </div>

                    <a href="{{ route('reports.statistics') }}"
                       class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                        <span class="material-icons-outlined text-sm mr-1">insights</span>
                        Full statistics
                    </a>
                </div>
            </div>

            <div class="p-4">
                <form method="GET" action="{{ route('dashboard') }}" class="space-y-2 mb-4"
                      x-data="{ custom: {{ $isCustom ? 'true' : 'false' }} }">
                    <div class="flex flex-wrap items-center gap-2">
                        <select name="date_filter"
                                @change="custom = ($event.target.value === 'custom')"
                                class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="last_30" @selected($dateFilter === 'last_30')>Last 30 days</option>
                            <option value="last_90" @selected($dateFilter === 'last_90')>Last 90 days</option>
                            <option value="last_6_months" @selected($dateFilter === 'last_6_months')>Last 6 months</option>
                            <option value="last_year" @selected($dateFilter === 'last_year')>Last 12 months</option>
                            <option value="all" @selected($dateFilter === 'all')>All time</option>
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

                        @if($statuses->isNotEmpty())
                            <select name="status_filter"
                                    class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="">Any status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected(request('status_filter') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        @endif

                        <button type="submit"
                                class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                            <span class="material-icons-outlined text-xs mr-1">filter_alt</span>
                            Apply
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('dashboard') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                        @endif
                    </div>

                    {{-- Revealed by the select rather than submitting on change, so the
                         date inputs exist before the page reloads. --}}
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
                            <p class="text-2xl font-bold text-gray-800 mt-0.5" data-count-to="{{ $value }}">0</p>

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
            {{-- Over time, and the largest events --}}
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
                        <p class="text-[11px] text-gray-500 mt-0.5">Largest in this range.</p>
                    </div>
                    <div class="p-4">
                        @if($topEvents->isNotEmpty())
                            <div style="position: relative; height: 260px;">
                                <canvas id="topEventsChart"></canvas>
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

            {{-- Who registered --}}
            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Who registered</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        {{ number_format($demographics['total']) }} participants across these events.
                        Blank answers are grouped as "Not stated" rather than left out.
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

            {{-- Age, and certificate coverage --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Age</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Participants who gave a date of birth. Bands are ordered by age, not size.
                        </p>
                    </div>
                    <div class="p-4">
                        @if($ageGroups->isNotEmpty())
                            <div style="position: relative; height: 240px;">
                                <canvas id="ageChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[240px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">cake</span>
                                <p class="text-xs">No dates of birth recorded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Certificate coverage</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Share of each event's participants who hold one.</p>
                    </div>
                    <div class="p-4">
                        @if($coverageByEvent->isNotEmpty())
                            <div class="space-y-2.5 max-h-[240px] overflow-y-auto pr-1">
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
                                            <div class="h-2 rounded-full transition-all duration-700 ease-out {{ $row['percent'] >= 90 ? 'bg-green-600' : ($row['percent'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 data-bar-to="{{ min(100, $row['percent']) }}"
                                                 style="width: 0%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[240px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">card_membership</span>
                                <p class="text-xs">No certificates issued yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($isAdmin && $byOrganizer->isNotEmpty())
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">By organizer</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Participants registered under each organizer's events in this range.</p>
                    </div>
                    <div class="p-4">
                        <div style="position: relative; height: {{ max(160, $byOrganizer->count() * 42) }}px;">
                            <canvas id="organizerChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            {{-- App accounts --}}
            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Mobile app accounts</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Reported as counts rather than a chart: too few accounts have signed in for a
                        time series to mean anything yet.
                    </p>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach([
                        ['Accounts', $appAccounts['total'], 'phone_android', 'visible to you'],
                        ['Signed in at least once', $appAccounts['signed_in'], 'login', $appAccounts['total'] > 0 ? round($appAccounts['signed_in'] / $appAccounts['total'] * 100) . '% of accounts' : null],
                        ['Reachable from this range', $appAccounts['reachable_from_range'], 'link', 'have an account'],
                        ['Participants with an email', $appAccounts['participants_with_email'], 'alternate_email', 'could have one'],
                    ] as [$label, $value, $icon, $note])
                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">{{ $label }}</p>
                                <span class="material-icons-outlined text-gray-300 text-base">{{ $icon }}</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5" data-count-to="{{ $value }}">0</p>
                            @if($note)
                                <p class="text-[11px] text-gray-500 mt-1">{{ $note }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Upcoming, and latest sign-ups --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Coming up</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Next events by start date, whatever the filter above says.</p>
                    </div>
                    <div class="p-4">
                        @if($upcoming->isNotEmpty())
                            <div class="space-y-2.5">
                                @foreach($upcoming as $event)
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            @can('events.read')
                                                <a href="{{ route('event.show', $event['id']) }}"
                                                   class="text-xs text-gray-800 hover:text-primary-DEFAULT font-medium truncate block">{{ $event['name'] }}</a>
                                            @else
                                                <p class="text-xs text-gray-800 font-medium truncate">{{ $event['name'] }}</p>
                                            @endcan
                                            <p class="text-[11px] text-gray-500">
                                                {{ \Carbon\Carbon::parse($event['start_date'])->format('j M Y') }}
                                                @if($event['location']) &middot; {{ $event['location'] }} @endif
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs text-gray-700">{{ number_format($event['registered']) }}@if($event['capacity']) / {{ number_format($event['capacity']) }}@endif</p>
                                            <p class="text-[11px] text-gray-400">registered</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[140px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">event_available</span>
                                <p class="text-xs">Nothing scheduled ahead</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Latest registrations</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Most recent sign-ups in this range.</p>
                    </div>
                    <div class="p-4">
                        @if($recentRegistrations->isNotEmpty())
                            <div class="space-y-2.5">
                                @foreach($recentRegistrations as $participant)
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-800 font-medium truncate">{{ $participant->name }}</p>
                                            <p class="text-[11px] text-gray-500 truncate">{{ $participant->event->name ?? '—' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-[11px] text-gray-500">
                                                {{ \Carbon\Carbon::parse($participant->registration_date ?? $participant->created_at)->format('j M') }}
                                            </p>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded {{ $participant->registration_type === 'verified' ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ ucfirst($participant->registration_type ?? 'simplified') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[140px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">person_add</span>
                                <p class="text-xs">No registrations in this range</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gridColour = 'rgba(0, 0, 0, 0.06)';

            // Same palette as the statistics report, so a category keeps its colour
            // when moving between the two pages.
            const palette = [
                '#4f46e5', '#0d9488', '#f59e0b', '#ef4444', '#8b5cf6',
                '#06b6d4', '#65a30d', '#db2777', '#0284c7', '#ea580c',
                '#7c3aed', '#059669', '#c026d3', '#475569'
            ];

            /**
             * Run something once, the first time its element is scrolled into view.
             *
             * Charts are built here rather than on load so their entry animation is
             * actually seen: a chart drawn while it is below the fold has finished
             * animating by the time it is reached.
             */
            function whenVisible(el, build) {
                if (!el) {
                    return;
                }

                if (!('IntersectionObserver' in window)) {
                    build();
                    return;
                }

                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            observer.unobserve(entry.target);
                            build();
                        }
                    });
                }, { threshold: 0.25 });

                observer.observe(el);
            }

            const ease = { duration: 900, easing: 'easeOutQuart' };

            // ---- headline numbers count up when they come into view ----
            document.querySelectorAll('[data-count-to]').forEach(function (el) {
                const target = parseInt(el.dataset.countTo, 10) || 0;

                whenVisible(el, function () {
                    if (target === 0) {
                        el.textContent = '0';
                        return;
                    }

                    const started = performance.now();

                    function step(now) {
                        const progress = Math.min(1, (now - started) / 900);
                        // Same easing as the charts so the page settles together.
                        const eased = 1 - Math.pow(1 - progress, 4);
                        el.textContent = Math.round(target * eased).toLocaleString();

                        if (progress < 1) {
                            requestAnimationFrame(step);
                        }
                    }

                    requestAnimationFrame(step);
                });
            });

            // ---- coverage bars grow when they come into view ----
            document.querySelectorAll('[data-bar-to]').forEach(function (el) {
                whenVisible(el, function () {
                    requestAnimationFrame(() => { el.style.width = el.dataset.barTo + '%'; });
                });
            });

            function doughnut(canvasId, rows) {
                const canvas = document.getElementById(canvasId);

                if (!canvas || !rows.length) {
                    return;
                }

                whenVisible(canvas, function () {
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
                            animation: { ...ease, animateRotate: true, animateScale: true },
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
                });
            }

            function horizontalBar(canvasId, rows, colour, label) {
                const canvas = document.getElementById(canvasId);

                if (!canvas || !rows.length) {
                    return;
                }

                whenVisible(canvas, function () {
                    new Chart(canvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rows.map(r => r.label),
                            datasets: [{
                                label: label,
                                data: rows.map(r => r.count),
                                backgroundColor: colour,
                                borderRadius: 3,
                                maxBarThickness: 22
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: ease,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                                y: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 10 },
                                        callback: function (value) {
                                            const text = this.getLabelForValue(value);
                                            return text.length > 28 ? text.slice(0, 27) + '…' : text;
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            }

            @if($totals['events'] > 0)
            // ---- registrations and certificates over time ----
            const timelineCanvas = document.getElementById('timelineChart');

            if (timelineCanvas) {
                const registrations = @json($registrationSeries);
                const certificates = @json($certificateSeries);

                // One shared axis, so the two series line up month for month.
                const labels = [...new Set([
                    ...registrations.map(r => r.label),
                    ...certificates.map(r => r.label)
                ])];

                const byLabel = (rows) => {
                    const map = Object.fromEntries(rows.map(r => [r.label, r.count]));
                    return labels.map(l => map[l] ?? 0);
                };

                whenVisible(timelineCanvas, function () {
                    const ctx = timelineCanvas.getContext('2d');

                    // A vertical gradient under each line reads better than a flat
                    // wash when two filled series overlap.
                    const fill = (hex) => {
                        const g = ctx.createLinearGradient(0, 0, 0, 260);
                        g.addColorStop(0, hex + '40');
                        g.addColorStop(1, hex + '00');
                        return g;
                    };

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Registrations',
                                    data: byLabel(registrations),
                                    borderColor: '#4f46e5',
                                    backgroundColor: fill('#4f46e5'),
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: labels.length > 24 ? 0 : 3,
                                    pointHoverRadius: 5,
                                    pointBackgroundColor: '#4f46e5'
                                },
                                {
                                    label: 'Certificates',
                                    data: byLabel(certificates),
                                    borderColor: '#0d9488',
                                    backgroundColor: fill('#0d9488'),
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: labels.length > 24 ? 0 : 3,
                                    pointHoverRadius: 5,
                                    pointBackgroundColor: '#0d9488'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: ease,
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
                });
            }

            horizontalBar('topEventsChart', @json($topEvents), 'rgba(79, 70, 229, 0.75)', 'Participants');

            // ---- age bands, vertical so they read left to right by age ----
            const ageCanvas = document.getElementById('ageChart');

            if (ageCanvas) {
                const rows = @json($ageGroups);

                whenVisible(ageCanvas, function () {
                    new Chart(ageCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rows.map(r => r.label),
                            datasets: [{
                                label: 'Participants',
                                data: rows.map(r => r.count),
                                backgroundColor: 'rgba(13, 148, 136, 0.75)',
                                borderRadius: 3,
                                maxBarThickness: 40
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: ease,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                            }
                        }
                    });
                });
            }

            doughnut('genderChart', @json($demographics['gender']));
            doughnut('raceChart', @json($demographics['race']));
            doughnut('typeChart', @json($demographics['type']));

            @if($isAdmin && $byOrganizer->isNotEmpty())
            horizontalBar('organizerChart', @json($byOrganizer), 'rgba(139, 92, 246, 0.75)', 'Participants');
            @endif
            @endif
        });
    </script>
</x-app-layout>
