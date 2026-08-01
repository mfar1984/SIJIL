<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Analytics</span>
    </x-slot>

    <x-slot name="title">PWA Analytics</x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-indigo-500">analytics</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Analytics</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        How the mobile app is being used: who has an account, who has signed in, and what they can see.
                    </p>
                </div>
                @can('pwa_analytics.export')
                <a href="{{ route('pwa.analytics.export', request()->query()) }}"
                   class="bg-primary-DEFAULT hover:bg-primary-dark text-white h-9 px-3 rounded text-xs font-medium inline-flex items-center shrink-0">
                    <span class="material-icons-outlined text-sm mr-1">download</span>
                    Export CSV
                </a>
                @endcan
            </div>
        </div>

        <div class="p-6">
            @if(!$tablesExist)
                <div class="bg-yellow-50 border border-yellow-200 rounded p-6">
                    <div class="flex items-start">
                        <span class="material-icons-outlined text-yellow-600 mr-3">warning</span>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800">Database setup required</h3>
                            <p class="text-xs text-yellow-700 mt-1">
                                The PWA tables are missing. Run <code>php artisan migrate</code> to create them.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Filters --}}
                <form method="GET" class="flex flex-wrap items-center gap-2 mb-6">
                    @if($events->count() > 0)
                    <select name="event_id"
                            class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[16rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <option value="">All events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ $selectedEventId == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    <select name="date_range" id="dateRange"
                            class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @foreach($ranges as $value => $label)
                            <option value="{{ $value }}" {{ $dateRange === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                        <option value="custom" {{ $dateRange === 'custom' ? 'selected' : '' }}>Custom range</option>
                    </select>

                    <div id="customDateContainer" class="flex items-center gap-2 {{ $dateRange === 'custom' ? '' : 'hidden' }}">
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <span class="text-xs text-gray-500">to</span>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    </div>

                    <button type="submit"
                            class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs font-medium inline-flex items-center shrink-0">
                        <span class="material-icons-outlined text-sm mr-1">filter_alt</span>
                        Apply
                    </button>

                    @if($selectedEventId || $dateRange !== '30')
                    <a href="{{ route('pwa.analytics') }}"
                       class="h-9 px-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-xs font-medium inline-flex items-center shrink-0">
                        Reset
                    </a>
                    @endif
                </form>

                @if($selectedEventName)
                <p class="text-xs text-gray-600 mb-4">
                    Showing accounts reachable from <span class="font-medium">{{ $selectedEventName }}</span>.
                </p>
                @endif

                {{-- Headline numbers --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-500">App accounts</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Participants who can sign in to the app</p>
                    </div>

                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-500">Signed in at least once</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($summary['signed_in']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $summary['signed_in_percent'] }}% of all accounts</p>
                    </div>

                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-500">Never signed in</p>
                        <p class="text-2xl font-bold {{ $summary['never_signed_in'] > 0 ? 'text-amber-600' : 'text-gray-800' }} mt-1">
                            {{ number_format($summary['never_signed_in']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($summary['never_signed_in'] > 0)
                                They may not have their password yet
                            @else
                                Everyone has signed in
                            @endif
                        </p>
                    </div>

                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-500">Certificates visible in the app</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['certificates_reachable']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Downloadable by an account holder</p>
                    </div>
                </div>

                {{-- Account health --}}
                <div class="border border-gray-200 rounded mb-6">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-sm text-indigo-500 mr-2">fact_check</span>
                            Account health
                        </h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Matched to a participant record</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($summary['linked']) }}
                                <span class="text-xs font-normal text-gray-500">({{ $summary['linked_percent'] }}%)</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">These accounts see their events and certificates</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">No matching participant</p>
                            <p class="text-lg font-semibold {{ $summary['unlinked'] > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                                {{ number_format($summary['unlinked']) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">They sign in but the app looks empty</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Changed their password</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($summary['changed_password']) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Still on the generated password otherwise</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Deactivated</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($summary['inactive']) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Cannot sign in</p>
                        </div>
                    </div>
                </div>

                {{-- Demographics --}}
                <div class="border border-gray-200 rounded mb-6">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-sm text-indigo-500 mr-2">groups</span>
                            Who uses the app
                        </h3>
                    </div>

                    <div class="p-4 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Gender --}}
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-3">Gender</p>
                            @forelse($demographics['gender'] as $row)
                                <div class="mb-2">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="{{ !empty($row['blank']) ? 'text-gray-400' : 'text-gray-700' }}">{{ $row['label'] }}</span>
                                        <span class="text-gray-600">{{ number_format($row['count']) }} <span class="text-gray-400">({{ $row['percent'] }}%)</span></span>
                                    </div>
                                    <div class="h-1.5 bg-gray-200 rounded overflow-hidden">
                                        <div class="h-full {{ !empty($row['blank']) ? 'bg-gray-300' : 'bg-primary-DEFAULT' }}"
                                             style="width: {{ min(100, $row['percent']) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">No accounts yet</p>
                            @endforelse
                        </div>

                        {{--
                            Race is a doughnut rather than a bar list: there are 14 possible
                            values, so stacked bars would run off the card as soon as a few
                            different races are recorded.
                        --}}
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-3">Race</p>
                            @if($demographics['race']->count() > 0)
                                <div style="position: relative; height: 190px;">
                                    <canvas id="raceChart"></canvas>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-[190px] text-gray-400">
                                    <span class="material-icons-outlined text-3xl mb-1">pie_chart</span>
                                    <p class="text-xs">No accounts yet</p>
                                </div>
                            @endif
                        </div>

                        {{-- Age bands as a bar chart, ordered youngest to oldest. --}}
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-3">Age</p>
                            @if($demographics['age_bands']->count() > 0)
                                <div style="position: relative; height: 190px;">
                                    <canvas id="ageChart"></canvas>
                                </div>
                                @if($demographics['without_date_of_birth'] > 0)
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ number_format($demographics['without_date_of_birth']) }} account(s) have no date of birth
                                    </p>
                                @endif
                            @else
                                <div class="flex flex-col items-center justify-center h-[190px] text-gray-400">
                                    <span class="material-icons-outlined text-3xl mb-1">bar_chart</span>
                                    <p class="text-xs">Nobody has given a date of birth</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="px-4 py-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            Gender, race and date of birth are optional. Participants fill them in from
                            Settings &rsaquo; Personal Information in the app, so "Not stated" shrinks as profiles get completed.
                        </p>
                    </div>
                </div>

                {{-- Charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="border border-gray-200 rounded">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-sm text-indigo-500 mr-2">show_chart</span>
                                New accounts, last 12 months
                            </h3>
                        </div>
                        <div class="p-4">
                            @if($accountsByMonth->sum('count') > 0)
                                <div style="position: relative; height: 260px;">
                                    <canvas id="accountsByMonthChart"></canvas>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                    <span class="material-icons-outlined text-4xl mb-2">show_chart</span>
                                    <p class="text-xs">No accounts were created in the last 12 months</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-sm text-indigo-500 mr-2">trending_up</span>
                                Activity
                                <span class="font-normal text-gray-500 ml-1">
                                    ({{ \Carbon\Carbon::parse($startDate)->format('j M Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('j M Y') }})
                                </span>
                            </h3>
                        </div>
                        <div class="p-4">
                            @if($dailyActivity->sum('new_accounts') + $dailyActivity->sum('sign_ins') > 0)
                                <div style="position: relative; height: 260px;">
                                    <canvas id="dailyActivityChart"></canvas>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                                    <span class="material-icons-outlined text-4xl mb-2">trending_flat</span>
                                    <p class="text-xs">Nothing happened in this range</p>
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 mt-3">
                                Sign-ins count accounts whose most recent sign-in falls on that day. The app stores only
                                the latest sign-in, not a full history.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Event reach --}}
                <div class="border border-gray-200 rounded mb-6">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-sm text-indigo-500 mr-2">event_available</span>
                            App coverage by event
                        </h3>
                    </div>

                    @if($eventReach->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="text-left font-medium py-2 px-4">Event</th>
                                    <th class="text-right font-medium py-2 px-4 w-32">Participants</th>
                                    <th class="text-right font-medium py-2 px-4 w-32">With an account</th>
                                    <th class="text-left font-medium py-2 px-4 w-48">Coverage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($eventReach as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 text-gray-800">{{ $row['name'] }}</td>
                                    <td class="py-2 px-4 text-right text-gray-700">{{ number_format($row['participants']) }}</td>
                                    <td class="py-2 px-4 text-right text-gray-700">{{ number_format($row['accounts']) }}</td>
                                    <td class="py-2 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-gray-200 rounded overflow-hidden">
                                                <div class="h-full bg-primary-DEFAULT" style="width: {{ min(100, $row['coverage']) }}%"></div>
                                            </div>
                                            <span class="text-gray-600 w-10 text-right">{{ $row['coverage'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            An account is counted against an event when its email matches a participant on that event.
                            This is the same rule the mobile app uses to decide what a participant can see.
                        </p>
                    </div>
                    @else
                    <div class="p-6 text-center text-gray-400">
                        <span class="material-icons-outlined text-4xl mb-2">event_busy</span>
                        <p class="text-xs">No events with participants yet</p>
                    </div>
                    @endif
                </div>

                {{-- Recent activity --}}
                <div class="border border-gray-200 rounded">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-sm text-indigo-500 mr-2">history</span>
                            Recent activity
                        </h3>
                    </div>

                    @if($recentActivity->count() > 0)
                    <ul class="divide-y divide-gray-100">
                        @foreach($recentActivity as $item)
                        <li class="flex items-start gap-3 px-4 py-3">
                            <span class="material-icons-outlined text-base {{ $item['type'] === 'sign_in' ? 'text-green-600' : 'text-indigo-500' }}">
                                {{ $item['icon'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-800">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $item['detail'] }}</p>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">
                                {{ $item['at']->diffForHumans() }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="p-6 text-center text-gray-400">
                        <span class="material-icons-outlined text-4xl mb-2">inbox</span>
                        <p class="text-xs">No app activity recorded yet</p>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Show the custom date inputs only when "Custom range" is picked.
            const rangeSelect = document.getElementById('dateRange');
            const customBox = document.getElementById('customDateContainer');

            if (rangeSelect && customBox) {
                rangeSelect.addEventListener('change', function () {
                    customBox.classList.toggle('hidden', this.value !== 'custom');
                });
            }

            const gridColour = 'rgba(0, 0, 0, 0.06)';

            @if($tablesExist && $demographics['race']->count() > 0)
            const raceCanvas = document.getElementById('raceChart');
            if (raceCanvas) {
                const race = @json($demographics['race']);

                // A fixed palette so a race keeps the same colour between loads.
                const palette = [
                    '#4f46e5', '#0d9488', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#06b6d4', '#65a30d', '#db2777', '#0284c7', '#ea580c',
                    '#7c3aed', '#059669', '#c026d3', '#475569'
                ];

                let next = 0;
                const colours = race.map(r => r.blank ? '#d1d5db' : palette[next++ % palette.length]);

                new Chart(raceCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: race.map(r => r.label),
                        datasets: [{
                            data: race.map(r => r.count),
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
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    padding: 8,
                                    font: { size: 10 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const row = race[ctx.dataIndex];
                                        return ` ${row.label}: ${row.count} (${row.percent}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @endif

            @if($tablesExist && $demographics['age_bands']->count() > 0)
            const ageCanvas = document.getElementById('ageChart');
            if (ageCanvas) {
                const ages = @json($demographics['age_bands']);

                new Chart(ageCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ages.map(r => r.label),
                        datasets: [{
                            label: 'Accounts',
                            data: ages.map(r => r.count),
                            backgroundColor: 'rgba(13, 148, 136, 0.75)',
                            borderRadius: 3,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColour } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
            @endif

            @if($tablesExist && $accountsByMonth->sum('count') > 0)
            const monthCanvas = document.getElementById('accountsByMonthChart');
            if (monthCanvas) {
                const monthly = @json($accountsByMonth);

                new Chart(monthCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: monthly.map(r => r.label),
                        datasets: [{
                            label: 'New accounts',
                            data: monthly.map(r => r.count),
                            backgroundColor: 'rgba(79, 70, 229, 0.75)',
                            borderRadius: 3,
                            maxBarThickness: 28
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColour } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
            @endif

            @if($tablesExist && $dailyActivity->sum('new_accounts') + $dailyActivity->sum('sign_ins') > 0)
            const dailyCanvas = document.getElementById('dailyActivityChart');
            if (dailyCanvas) {
                const daily = @json($dailyActivity);

                new Chart(dailyCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: daily.map(r => r.label),
                        datasets: [
                            {
                                label: 'New accounts',
                                data: daily.map(r => r.new_accounts),
                                borderColor: 'rgb(79, 70, 229)',
                                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: daily.length > 60 ? 0 : 3
                            },
                            {
                                label: 'Sign-ins',
                                data: daily.map(r => r.sign_ins),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: daily.length > 60 ? 0 : 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColour } },
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } }
                        }
                    }
                });
            }
            @endif
        });
    </script>
</x-app-layout>
