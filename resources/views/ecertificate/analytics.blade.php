<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Analytics</span>
    </x-slot>

    <x-slot name="title">App Analytics</x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @php
        $isCustom = request('date_range') === 'custom';
        $hasFilters = request()->filled('event_id') || request()->filled('date_range');

        // Profile fields are optional, and most are empty. Reporting how complete
        // they are is more useful than a chart of nine answers, and it tells an
        // organizer what to chase.
        $completeness = [
            ['Gender', $summary['total'] - ($demographics['gender']->firstWhere('blank', true)['count'] ?? 0), 'wc'],
            ['Date of birth', $demographics['with_date_of_birth'], 'cake'],
            ['Race', $summary['total'] - ($demographics['race']->firstWhere('blank', true)['count'] ?? 0), 'diversity_3'],
        ];
    @endphp

    <div class="space-y-3">
        {{-- Header, filter and headline figures --}}
        <div class="bg-white rounded shadow-md border border-gray-300">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center">
                            <span class="material-icons-outlined mr-2 text-primary-DEFAULT">phone_android</span>
                            <h1 class="text-xl font-bold text-gray-800">App Analytics</h1>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-8">
                            @if($selectedEventName)
                                Accounts reachable from <span class="font-medium">{{ $selectedEventName }}</span>.
                            @else
                                Every app account you can see.
                            @endif
                            Accounts are matched to participants by email address, which is how the app
                            itself resolves who owns which certificate.
                        </p>
                    </div>

                    <a href="{{ route('pwa.participants') }}"
                       class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                        <span class="material-icons-outlined text-sm mr-1">groups</span>
                        Manage accounts
                    </a>
                </div>
            </div>

            <div class="p-4">
                @if(!$tablesExist)
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded text-xs">
                        The app account tables are not present on this installation, so there is nothing to report yet.
                    </div>
                @else
                    <form method="GET" action="{{ route('pwa.analytics') }}" class="space-y-2 mb-4"
                          x-data="{ custom: {{ $isCustom ? 'true' : 'false' }} }">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($events->isNotEmpty())
                                <select name="event_id"
                                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[16rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="">All events</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected($selectedEventId === $event->id)>{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <select name="date_range"
                                    @change="custom = ($event.target.value === 'custom')"
                                    class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                @foreach($ranges as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $dateRange === (string) $value)>{{ $label }}</option>
                                @endforeach
                                <option value="custom" @selected($isCustom)>Custom range</option>
                            </select>

                            <button type="submit"
                                    class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0">
                                <span class="material-icons-outlined text-xs mr-1">filter_alt</span>
                                Apply
                            </button>

                            @if($hasFilters)
                                <a href="{{ route('pwa.analytics') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2" x-show="custom" x-cloak>
                            <label class="text-xs text-gray-600">From</label>
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                   class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <label class="text-xs text-gray-600">to</label>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                   class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <span class="text-[11px] text-gray-500">Press Apply to use this range.</span>
                        </div>
                    </form>

                    {{-- Each tile says which period it answers. The date filter used to
                         reach only the activity chart, so these looked inert. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">App accounts</p>
                                <span class="material-icons-outlined text-gray-300 text-base">phone_android</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5" data-count-to="{{ $summary['total'] }}">0</p>
                            <p class="text-[11px] text-gray-500 mt-1">
                                All time.
                                @if($summary['created_in_range'] !== $summary['total'])
                                    {{ number_format($summary['created_in_range']) }} created in this range.
                                @endif
                            </p>
                        </div>

                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">Signed in at least once</p>
                                <span class="material-icons-outlined text-gray-300 text-base">login</span>
                            </div>
                            <p class="text-2xl font-bold {{ $summary['signed_in'] > 0 ? 'text-green-600' : 'text-gray-800' }} mt-0.5"
                               data-count-to="{{ $summary['signed_in'] }}">0</p>
                            <p class="text-[11px] text-gray-500 mt-1">
                                {{ $summary['signed_in_percent'] }}% of accounts, all time.
                                @if($summary['signed_in_range'] !== $summary['signed_in'])
                                    {{ number_format($summary['signed_in_range']) }} in this range.
                                @endif
                            </p>
                        </div>

                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">Linked to a participant</p>
                                <span class="material-icons-outlined text-gray-300 text-base">link</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5" data-count-to="{{ $summary['linked'] }}">0</p>
                            <p class="text-[11px] {{ $summary['unlinked'] > 0 ? 'text-amber-600' : 'text-gray-500' }} mt-1">
                                @if($summary['unlinked'] > 0)
                                    {{ number_format($summary['unlinked']) }} see an empty app
                                @else
                                    every account has records
                                @endif
                            </p>
                        </div>

                        <div class="border border-gray-200 rounded p-4">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-gray-500">Certificates in reach</p>
                                <span class="material-icons-outlined text-gray-300 text-base">card_membership</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5" data-count-to="{{ $summary['certificates_reachable'] }}">0</p>
                            <p class="text-[11px] text-gray-500 mt-1">Downloadable by an account holder</p>
                        </div>
                    </div>

                    @if($summary['unlinked'] > 0)
                        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded mt-3 text-xs">
                            {{ number_format($summary['unlinked']) }}
                            {{ $summary['unlinked'] === 1 ? 'account has' : 'accounts have' }}
                            no matching participant record, so signing in shows an empty list. That happens when
                            the account email differs from the one used to register.
                        </div>
                    @endif

                    @if($summary['inactive'] > 0)
                        <p class="text-[11px] text-gray-500 mt-3">
                            {{ number_format($summary['inactive']) }} of these
                            {{ $summary['inactive'] === 1 ? 'account is' : 'accounts are' }}
                            inactive or suspended and cannot sign in.
                        </p>
                    @endif
                @endif
            </div>
        </div>

        @if($tablesExist && $summary['total'] === 0)
            <div class="bg-white rounded shadow-md border border-gray-300 p-10 text-center">
                <span class="material-icons-outlined text-4xl text-gray-300">phonelink_erase</span>
                <p class="text-sm text-gray-600 mt-2">No app accounts to report on.</p>
                <p class="text-xs text-gray-500 mt-1">
                    Create them under PWA › Participants, or switch on "Create mobile app account" on an event.
                </p>
            </div>
        @elseif($tablesExist)
            {{-- Accounts over time, and how far the app reaches into each event --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Accounts created</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Last 12 months, whatever range is selected above.</p>
                    </div>
                    <div class="p-4">
                        @if($accountsByMonth->sum('count') > 0)
                            <div style="position: relative; height: 260px;">
                                <canvas id="accountsChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">show_chart</span>
                                <p class="text-xs">No accounts created in the last 12 months</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">App reach by event</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            How many of each event's participants hold an account. Ranks events against
                            each other, so the event filter does not apply here.
                        </p>
                    </div>
                    <div class="p-4">
                        @if($eventReach->isNotEmpty())
                            <div class="space-y-2.5 max-h-[260px] overflow-y-auto pr-1">
                                @foreach($eventReach as $row)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-700 truncate pr-2">{{ $row['name'] }}</span>
                                            <span class="text-gray-500 shrink-0">
                                                {{ number_format($row['accounts']) }} of {{ number_format($row['participants']) }}
                                                ({{ $row['coverage'] }}%)
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-700 ease-out {{ $row['coverage'] >= 75 ? 'bg-green-600' : ($row['coverage'] >= 30 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 data-bar-to="{{ min(100, $row['coverage']) }}"
                                                 style="width: 0%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                <span class="material-icons-outlined text-3xl mb-1">event_busy</span>
                                <p class="text-xs">No events with participants yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Day by day --}}
            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Activity</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        {{ \Carbon\Carbon::parse($startDate)->format('j M Y') }} –
                        {{ \Carbon\Carbon::parse($endDate)->format('j M Y') }}.
                        Sign-ins count accounts whose most recent sign-in fell on that day: the app keeps
                        only the latest timestamp, not a history.
                    </p>
                </div>
                <div class="p-4">
                    @if($dailyActivity->sum('new_accounts') > 0 || $dailyActivity->sum('sign_ins') > 0)
                        <div style="position: relative; height: 240px;">
                            <canvas id="activityChart"></canvas>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[240px] text-gray-400">
                            <span class="material-icons-outlined text-3xl mb-1">timeline</span>
                            <p class="text-xs">Nothing happened in this range</p>
                            <p class="text-[11px] mt-1">Widen the range, or check that participants have been told their password.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Profile completeness, then whatever demographics are worth drawing --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Profile completeness</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            These fields are optional and filled in by the account holder from
                            Settings › Personal Information in the app.
                        </p>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($completeness as [$label, $filled, $icon])
                            @php $percent = $summary['total'] > 0 ? round($filled / $summary['total'] * 100) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-700 flex items-center">
                                        <span class="material-icons-outlined text-gray-400 text-sm mr-1">{{ $icon }}</span>
                                        {{ $label }}
                                    </span>
                                    <span class="text-gray-500">
                                        {{ number_format($filled) }} of {{ number_format($summary['total']) }} ({{ $percent }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-primary-DEFAULT transition-all duration-700 ease-out"
                                         data-bar-to="{{ $percent }}" style="width: 0%"></div>
                                </div>
                            </div>
                        @endforeach

                        <p class="text-[11px] text-gray-500 pt-1">
                            Low numbers here are why the breakdowns opposite are thin. Nothing is broken:
                            most people have simply not opened their profile.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded shadow-md border border-gray-300">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-700">Who holds an account</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Drawn only where enough people have answered. "Not stated" is shown rather
                            than hidden, so the shape of the gap is visible.
                        </p>
                    </div>
                    <div class="p-4">
                        @if($demographics['age_bands']->isNotEmpty())
                            <p class="text-xs font-medium text-gray-700 mb-2">
                                Age
                                <span class="font-normal text-gray-400">
                                    &middot; {{ number_format($demographics['with_date_of_birth']) }} of {{ number_format($summary['total']) }} gave a date of birth
                                </span>
                            </p>
                            <div style="position: relative; height: 170px;">
                                <canvas id="ageChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-[100px] text-gray-400">
                                <span class="material-icons-outlined text-2xl mb-1">cake</span>
                                <p class="text-xs">No dates of birth recorded</p>
                            </div>
                        @endif

                        {{-- Gender as bars, not a doughnut: with a handful of answers a
                             pie chart implies a precision the data has not earned. --}}
                        @if($demographics['gender']->isNotEmpty())
                            <p class="text-xs font-medium text-gray-700 mt-4 mb-2">Gender</p>
                            <div class="space-y-1.5">
                                @foreach($demographics['gender'] as $row)
                                    <div>
                                        <div class="flex items-center justify-between text-[11px] mb-0.5">
                                            <span class="{{ ($row['blank'] ?? false) ? 'text-gray-400' : 'text-gray-700' }}">{{ $row['label'] }}</span>
                                            <span class="text-gray-500">{{ number_format($row['count']) }} ({{ $row['percent'] }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all duration-700 ease-out {{ ($row['blank'] ?? false) ? 'bg-gray-300' : 'bg-indigo-500' }}"
                                                 data-bar-to="{{ $row['percent'] }}" style="width: 0%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Feed --}}
            <div class="bg-white rounded shadow-md border border-gray-300">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Recent activity</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">Newest accounts and most recent sign-ins, together.</p>
                </div>
                <div class="p-4">
                    @if($recentActivity->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($recentActivity as $item)
                                <div class="flex items-start gap-3">
                                    <span class="material-icons-outlined text-base shrink-0 mt-0.5 {{ $item['type'] === 'account' ? 'text-indigo-400' : 'text-green-500' }}">
                                        {{ $item['icon'] }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-gray-800">{{ $item['title'] }}</p>
                                        <p class="text-[11px] text-gray-500 truncate">{{ $item['detail'] }}</p>
                                    </div>
                                    <p class="text-[11px] text-gray-400 shrink-0"
                                       title="{{ \Carbon\Carbon::parse($item['at'])->format('j M Y, H:i') }}">
                                        {{ \Carbon\Carbon::parse($item['at'])->diffForHumans(short: true) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[140px] text-gray-400">
                            <span class="material-icons-outlined text-3xl mb-1">history</span>
                            <p class="text-xs">Nothing recorded yet</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gridColour = 'rgba(0, 0, 0, 0.06)';
            const ease = { duration: 900, easing: 'easeOutQuart' };

            /**
             * Build once, the first time the element is scrolled into view, so the
             * entry animation is actually seen rather than finishing off-screen.
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
                        const eased = 1 - Math.pow(1 - progress, 4);
                        el.textContent = Math.round(target * eased).toLocaleString();

                        if (progress < 1) {
                            requestAnimationFrame(step);
                        }
                    }

                    requestAnimationFrame(step);
                });
            });

            document.querySelectorAll('[data-bar-to]').forEach(function (el) {
                whenVisible(el, function () {
                    requestAnimationFrame(() => { el.style.width = el.dataset.barTo + '%'; });
                });
            });

            @if($tablesExist && $summary['total'] > 0)
            // ---- accounts created per month ----
            const accountsCanvas = document.getElementById('accountsChart');

            if (accountsCanvas) {
                const rows = @json($accountsByMonth);

                whenVisible(accountsCanvas, function () {
                    const ctx = accountsCanvas.getContext('2d');
                    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
                    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: rows.map(r => r.label),
                            datasets: [{
                                label: 'Accounts created',
                                data: rows.map(r => r.count),
                                borderColor: '#4f46e5',
                                backgroundColor: gradient,
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointBackgroundColor: '#4f46e5'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: ease,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                            }
                        }
                    });
                });
            }

            // ---- day by day activity ----
            const activityCanvas = document.getElementById('activityChart');

            if (activityCanvas) {
                const rows = @json($dailyActivity);

                whenVisible(activityCanvas, function () {
                    const ctx = activityCanvas.getContext('2d');

                    const fill = (hex) => {
                        const g = ctx.createLinearGradient(0, 0, 0, 240);
                        g.addColorStop(0, hex + '40');
                        g.addColorStop(1, hex + '00');
                        return g;
                    };

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: rows.map(r => r.label),
                            datasets: [
                                {
                                    label: 'New accounts',
                                    data: rows.map(r => r.new_accounts),
                                    borderColor: '#4f46e5',
                                    backgroundColor: fill('#4f46e5'),
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: rows.length > 40 ? 0 : 3,
                                    pointHoverRadius: 5
                                },
                                {
                                    label: 'Sign-ins',
                                    data: rows.map(r => r.sign_ins),
                                    borderColor: '#0d9488',
                                    backgroundColor: fill('#0d9488'),
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: rows.length > 40 ? 0 : 3,
                                    pointHoverRadius: 5
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
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 10 },
                                        autoSkip: true,
                                        maxTicksLimit: 12,
                                        maxRotation: 0
                                    }
                                }
                            }
                        }
                    });
                });
            }

            // ---- age bands ----
            const ageCanvas = document.getElementById('ageChart');

            if (ageCanvas) {
                const rows = @json($demographics['age_bands']);

                whenVisible(ageCanvas, function () {
                    new Chart(ageCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rows.map(r => r.label),
                            datasets: [{
                                label: 'Accounts',
                                data: rows.map(r => r.count),
                                backgroundColor: 'rgba(13, 148, 136, 0.75)',
                                borderRadius: 3,
                                maxBarThickness: 34
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
            @endif
        });
    </script>
</x-app-layout>
