<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance Reports</span>
    </x-slot>

    <x-slot name="title">Attendance Reports</x-slot>

    @php
        // Carried into the export so the CSV matches exactly what is on screen.
        $filters = ['search', 'event_filter', 'date_filter', 'rate_filter'];
        $hasFilters = collect($filters)->contains(fn ($f) => request()->filled($f));
    @endphp

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">assignment</span>
                        <h1 class="text-xl font-bold text-gray-800">Attendance Reports</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Check-in sessions, who turned up, and how that compares with registrations.</p>
                </div>

                @can('attendance_reports.export')
                    {{-- A real CSV of the filtered sessions. This used to be
                         alert('Exporting attendance report...'). --}}
                    <form method="POST" action="{{ route('reports.attendance.export') }}">
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
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Check-in sessions</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalSessions) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Matching the current filters</p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Registered</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalRegistered) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Participants on these events</p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Checked in</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalAttendees) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Distinct people marked present</p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Attendance rate</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $averageAttendanceRate }}%</p>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                        <div class="bg-primary-DEFAULT h-1.5 rounded-full" style="width: {{ min(100, $averageAttendanceRate) }}%"></div>
                    </div>
                </div>
            </div>

            @if($totalSessions > 0 && $totalAttendees === 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded mb-4 text-xs">
                    These sessions exist but no check-in has been recorded against any of them yet, so every
                    rate below is 0%. Scans write to the attendance records; until one arrives there is
                    nothing to measure.
                </div>
            @endif

            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('reports.attendance.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search event name, location..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="event_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @if(request('event_filter') == $event->id) selected @endif>{{ $event->name }}</option>
                    @endforeach
                </select>

                <select name="date_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Any date</option>
                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                    <option value="week" @if(request('date_filter') == 'week') selected @endif>Last 7 days</option>
                    <option value="month" @if(request('date_filter') == 'month') selected @endif>This month</option>
                    <option value="upcoming" @if(request('date_filter') == 'upcoming') selected @endif>Upcoming</option>
                    <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                </select>

                {{-- This filter was read from the request and discarded, behind a
                     comment saying it would be handled later. It works now, and the
                     thresholds below match what it actually does. --}}
                <select name="rate_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[12rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Any rate</option>
                    <option value="high" @if(request('rate_filter') == 'high') selected @endif>High (75% and up)</option>
                    <option value="medium" @if(request('rate_filter') == 'medium') selected @endif>Medium (40–74%)</option>
                    <option value="low" @if(request('rate_filter') == 'low') selected @endif>Low (under 40%)</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if($hasFilters)
                    <a href="{{ route('reports.attendance.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            @if($hasFilters)
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Filtered:</span>
                    @if(request('search'))
                        <span class="ml-2">"{{ request('search') }}"</span>
                    @endif
                    @if(request('event_filter'))
                        <span class="ml-2">Event: {{ $events->firstWhere('id', (int) request('event_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(request('date_filter')) }}</span>
                    @endif
                    @if(request('rate_filter'))
                        <span class="ml-2">Rate: {{ ucfirst(request('rate_filter')) }}</span>
                    @endif
                    <span class="ml-2">({{ number_format($sessions->total()) }} sessions)</span>
                </div>
            @endif

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event</th>
                            <th class="py-3 px-4 text-left">Session date</th>
                            {{-- These two columns render the check-in window, which the old
                                 headers called simply "Start Time" and "End Time". --}}
                            <th class="py-3 px-4 text-left">Check-in window</th>
                            <th class="py-3 px-4 text-left">Check-out window</th>
                            <th class="py-3 px-4 text-left">Registered</th>
                            <th class="py-3 px-4 text-left">Checked in</th>
                            <th class="py-3 px-4 text-left">Rate</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tableRows as $row)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-800">{{ $row['event_name'] }}</div>
                                    @if($row['event_location'])
                                        <div class="text-gray-500">{{ $row['event_location'] }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    {{ $row['session_date'] ? \Carbon\Carbon::parse($row['session_date'])->format('d M Y') : '—' }}
                                </td>
                                {{-- substr() on a null time raised a deprecation on every row
                                     where the window was not set. --}}
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($row['checkin_from'] || $row['checkin_to'])
                                        {{ substr((string) $row['checkin_from'], 0, 5) ?: '—' }}
                                        –
                                        {{ substr((string) $row['checkin_to'], 0, 5) ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($row['checkout_from'] || $row['checkout_to'])
                                        {{ substr((string) $row['checkout_from'], 0, 5) ?: '—' }}
                                        –
                                        {{ substr((string) $row['checkout_to'], 0, 5) ?: '—' }}
                                    @else
                                        <span class="text-gray-400">Not used</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ number_format($row['registered']) }}</td>
                                <td class="py-3 px-4">{{ number_format($row['attended']) }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 shrink-0">
                                            <div class="h-2 rounded-full {{ $row['rate'] >= 75 ? 'bg-green-600' : ($row['rate'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 style="width: {{ min(100, $row['rate']) }}%"></div>
                                        </div>
                                        <span class="ml-2">{{ $row['rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('reports.attendance.show', ['id' => $row['id']]) }}"
                                           class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View details">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('attendance_reports.delete')
                                            <form method="POST" action="{{ route('reports.attendance.delete', $row['id']) }}"
                                                  onsubmit="return confirm('Delete this session and its check-in records? This cannot be undone.')"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                    <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="8" class="py-8 text-center text-gray-500">
                                    @if($hasFilters)
                                        No sessions match these filters.
                                    @else
                                        No attendance sessions yet. Create one from an event to start recording check-ins.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(request('rate_filter'))
                <p class="text-[11px] text-gray-500 mt-2">
                    The rate filter applies to the sessions on this page, so page counts below still refer to
                    the unfiltered set.
                </p>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($sessions->total() > 0)
                        Showing <span class="font-medium">{{ $sessions->firstItem() }}</span>
                        to <span class="font-medium">{{ $sessions->lastItem() }}</span>
                        of <span class="font-medium">{{ number_format($sessions->total()) }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $sessions->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    {{-- The delete confirmation modal that used to sit here, together with
         confirmDelete() and closeDeleteModal(), was never opened by anything: each
         row submits its own inline form with a confirm() instead. Removed. --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => this.form.submit(), 500);
                });
            }
        });
    </script>
</x-app-layout>
