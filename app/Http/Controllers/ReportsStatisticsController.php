<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Event statistics.
 *
 * Rewritten. The previous version had two structural problems.
 *
 * It called paginate() on the event query and then called count() and pluck() on
 * that same builder afterwards, so the limit and offset were still attached: every
 * summary card described only the rows on the current page, and "Total Events" read
 * 0 from page two onwards.
 *
 * And it computed attendance with four levels of nested loops - events, then
 * attendances, then sessions, then a count per session - repeated four times over
 * for the current period, the previous period, five event types and a top-events
 * list, plus twelve more count() calls for the monthly chart. Everything here is
 * grouped aggregates instead.
 *
 * The date range selects events by start_date. Every figure on the page then
 * describes that set of events, which is the only reading that stays coherent when
 * participants, certificates and sessions all carry dates of their own.
 */
class ReportsStatisticsController extends Controller
{
    /**
     * Ranges offered by the filter, in days back from today.
     */
    private const RANGES = [
        'last_30' => 30,
        'last_90' => 90,
        'last_6_months' => 182,
        'last_year' => 365,
        'all' => null,
    ];

    public function index(Request $request)
    {
        [$start, $end, $rangeLabel] = $this->resolveRange($request);

        $eventQuery = $this->eventQuery($request, $start, $end);

        // Aggregates come from their own clone, before any paging is applied.
        $eventIds = (clone $eventQuery)->pluck('id')->all();
        $totals = $this->totalsFor($eventIds);

        // The same measurements over the window immediately before this one, so the
        // change is a real comparison rather than a placeholder.
        $previous = null;

        if ($start && $end) {
            $length = $start->diffInDays($end) + 1;
            $prevEnd = (clone $start)->subDay();
            $prevStart = (clone $prevEnd)->subDays($length - 1);

            $prevIds = $this->eventQuery($request, $prevStart, $prevEnd)->pluck('id')->all();
            $previous = $this->totalsFor($prevIds);
            $previous['range'] = $prevStart->format('j M Y') . ' – ' . $prevEnd->format('j M Y');
        }

        $sort = in_array($request->get('sort'), ['participants', 'certificates', 'coverage', 'attendance', 'date'], true)
            ? $request->get('sort')
            : 'participants';

        return view('reports.statistics', [
            'events' => $this->eventTable($eventIds, $sort, (int) \App\Support\SystemSettings::perPage($request, 10), $request),
            'totals' => $totals,
            'previous' => $previous,
            'changes' => $this->changes($totals, $previous),
            'registrationSeries' => $this->registrationSeries($eventIds),
            'certificateSeries' => $this->certificateSeries($eventIds),
            'participantsByEvent' => $this->participantsByEvent($eventIds),
            'demographics' => $this->demographics($eventIds),
            'coverageByEvent' => $this->coverageByEvent($eventIds),
            'organizers' => auth()->user()->hasRole('Administrator')
                ? User::whereIn('id', Event::distinct()->pluck('user_id'))->orderBy('name')->get(['id', 'name'])
                : collect(),
            'statuses' => Event::whereIn('id', $eventIds)->distinct()->pluck('status')->filter()->sort()->values(),
            'rangeLabel' => $rangeLabel,
            'sort' => $sort,
        ]);
    }

    /**
     * The window the filter is asking for.
     *
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function resolveRange(Request $request): array
    {
        $filter = $request->get('date_filter', 'last_year');

        if ($filter === 'custom') {
            $start = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : now()->subDays(30)->startOfDay();

            $end = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfDay();

            // A backwards range would silently match nothing.
            if ($end->lt($start)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [$start, $end, $start->format('j M Y') . ' – ' . $end->format('j M Y')];
        }

        $days = self::RANGES[$filter] ?? 365;

        if ($days === null) {
            return [null, null, 'All time'];
        }

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        return [$start, $end, $start->format('j M Y') . ' – ' . $end->format('j M Y')];
    }

    /**
     * Events in scope for the current filters.
     */
    private function eventQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = Event::query();

        if (! auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('organizer')) {
            // The controller always read this parameter; the page never offered a
            // control for it, so the filter was unreachable.
            $query->where('user_id', $request->organizer);
        }

        if ($start && $end) {
            $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('location', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        return $query;
    }

    /**
     * Headline figures for a set of events, in four queries regardless of size.
     */
    private function totalsFor(array $eventIds): array
    {
        if (! $eventIds) {
            return [
                'events' => 0, 'participants' => 0, 'certificates' => 0,
                'checked_in' => 0, 'sessions' => 0, 'attendance_rate' => 0.0,
                'coverage_rate' => 0.0,
            ];
        }

        $participants = Participant::whereIn('event_id', $eventIds)->count();
        $certificates = Certificate::whereIn('event_id', $eventIds)->count();

        $sessionIds = DB::table('attendance_sessions')
            ->join('attendances', 'attendance_sessions.attendance_id', '=', 'attendances.id')
            ->whereIn('attendances.event_id', $eventIds)
            ->pluck('attendance_sessions.id');

        $checkedIn = $sessionIds->isEmpty() ? 0 : DB::table('attendance_records')
            ->whereIn('attendance_session_id', $sessionIds)
            ->where('status', 'present')
            ->distinct()
            ->count('participant_id');

        return [
            'events' => count($eventIds),
            'participants' => $participants,
            'certificates' => $certificates,
            'checked_in' => $checkedIn,
            'sessions' => $sessionIds->count(),
            'attendance_rate' => $participants > 0 ? round(($checkedIn / $participants) * 100, 1) : 0.0,
            'coverage_rate' => $participants > 0 ? round(($certificates / $participants) * 100, 1) : 0.0,
        ];
    }

    /**
     * Percentage change per metric, or null when there is nothing to compare with.
     *
     * The old code fell back to a literal 100 whenever the previous period was
     * empty, so a page with no history claimed a 100% increase in everything.
     */
    private function changes(array $totals, ?array $previous): array
    {
        $changes = [];

        foreach (['events', 'participants', 'certificates'] as $metric) {
            if ($previous === null || ($previous[$metric] ?? 0) === 0) {
                $changes[$metric] = null;

                continue;
            }

            $changes[$metric] = round((($totals[$metric] - $previous[$metric]) / $previous[$metric]) * 100, 1);
        }

        return $changes;
    }

    /**
     * Registrations per month for these events.
     */
    private function registrationSeries(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        return Participant::whereIn('event_id', $eventIds)
            ->selectRaw("DATE_FORMAT(COALESCE(registration_date, created_at), '%Y-%m') as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->bucket)->format('M Y'),
                'count' => (int) $row->total,
            ]);
    }

    /**
     * Certificates issued per month for these events.
     */
    private function certificateSeries(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        return Certificate::whereIn('event_id', $eventIds)
            ->whereNotNull('generated_at')
            ->selectRaw("DATE_FORMAT(generated_at, '%Y-%m') as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->bucket)->format('M Y'),
                'count' => (int) $row->total,
            ]);
    }

    /**
     * Participants per event, largest first.
     */
    private function participantsByEvent(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $counts = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        return Event::whereIn('id', $eventIds)->get(['id', 'name'])
            ->map(fn ($event) => [
                'label' => $event->name,
                'count' => (int) ($counts[$event->id] ?? 0),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values();
    }

    /**
     * Certificate coverage per event.
     */
    private function coverageByEvent(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $participants = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $certificates = Certificate::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(DISTINCT participant_id) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        return Event::whereIn('id', $eventIds)->get(['id', 'name'])
            ->map(function ($event) use ($participants, $certificates) {
                $registered = (int) ($participants[$event->id] ?? 0);
                $issued = (int) ($certificates[$event->id] ?? 0);

                return [
                    'label' => $event->name,
                    'registered' => $registered,
                    'issued' => $issued,
                    'percent' => $registered > 0 ? round(($issued / $registered) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('percent')
            ->values();
    }

    /**
     * Who the participants are.
     *
     * Gender, race and registration type are all recorded for the great majority of
     * participants, so these are the demographics worth charting. A blank flag lets
     * the view colour "Not stated" separately instead of implying it is a category.
     */
    private function demographics(array $eventIds): array
    {
        if (! $eventIds) {
            return ['gender' => collect(), 'race' => collect(), 'type' => collect(), 'total' => 0];
        }

        $total = Participant::whereIn('event_id', $eventIds)->count();

        $breakdown = function (string $column) use ($eventIds, $total) {
            return Participant::whereIn('event_id', $eventIds)
                ->selectRaw("COALESCE(NULLIF(TRIM({$column}), ''), '__blank__') as bucket, COUNT(*) as total")
                ->groupBy('bucket')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->bucket === '__blank__' ? 'Not stated' : ucwords((string) $row->bucket),
                    'count' => (int) $row->total,
                    'percent' => $total > 0 ? round(($row->total / $total) * 100, 1) : 0.0,
                    'blank' => $row->bucket === '__blank__',
                ])
                // "Not stated" last, so it never takes the first colour.
                ->sortBy(fn ($row) => $row['blank'] ? 1 : 0)
                ->values();
        };

        return [
            'gender' => $breakdown('gender'),
            'race' => $breakdown('race'),
            'type' => $breakdown('registration_type'),
            'total' => $total,
        ];
    }

    /**
     * The event table, sorted by a real metric.
     *
     * The old page called this "Top Performing Events" while showing the current
     * page of the event list ordered by start_date, which is not a performance
     * measure at all.
     */
    private function eventTable(array $eventIds, string $sort, int $perPage, Request $request)
    {
        if (! $eventIds) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, max(1, $perPage));
        }

        $participants = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $certificates = Certificate::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $sessionsByEvent = DB::table('attendance_sessions')
            ->join('attendances', 'attendance_sessions.attendance_id', '=', 'attendances.id')
            ->whereIn('attendances.event_id', $eventIds)
            ->select('attendances.event_id', 'attendance_sessions.id')
            ->get()
            ->groupBy('event_id');

        $presentBySession = DB::table('attendance_records')
            ->where('status', 'present')
            ->selectRaw('attendance_session_id, COUNT(DISTINCT participant_id) as total')
            ->groupBy('attendance_session_id')
            ->pluck('total', 'attendance_session_id');

        $rows = Event::whereIn('id', $eventIds)
            ->with('user:id,name')
            ->get()
            ->map(function ($event) use ($participants, $certificates, $sessionsByEvent, $presentBySession) {
                $registered = (int) ($participants[$event->id] ?? 0);
                $issued = (int) ($certificates[$event->id] ?? 0);

                $sessions = $sessionsByEvent->get($event->id, collect());
                $present = $sessions->sum(fn ($s) => (int) ($presentBySession[$s->id] ?? 0));
                $slots = $sessions->count() * max(1, $registered);

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'organizer' => $event->user->name ?? '—',
                    'status' => $event->status,
                    'start_date' => $event->start_date,
                    'participants' => $registered,
                    'certificates' => $issued,
                    'sessions' => $sessions->count(),
                    'coverage' => $registered > 0 ? round(($issued / $registered) * 100, 1) : 0.0,
                    'attendance' => $slots > 0 ? round(($present / $slots) * 100, 1) : 0.0,
                ];
            });

        $rows = (match ($sort) {
            'certificates' => $rows->sortByDesc('certificates'),
            'coverage' => $rows->sortByDesc('coverage'),
            'attendance' => $rows->sortByDesc('attendance'),
            'date' => $rows->sortByDesc('start_date'),
            default => $rows->sortByDesc('participants'),
        })->values();

        $page = max(1, (int) $request->get('page', 1));
        $perPage = max(1, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * CSV of the same rows the table shows, honouring every filter.
     *
     * The old export re-implemented the filters and left status_filter out, so the
     * file could disagree with the screen.
     */
    public function export(Request $request)
    {
        [$start, $end] = $this->resolveRange($request);

        $eventIds = $this->eventQuery($request, $start, $end)->pluck('id')->all();
        $rows = $this->eventTable($eventIds, $request->get('sort', 'participants'), max(1, count($eventIds)), $request);

        $filename = 'event-statistics-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Event', 'Location', 'Organizer', 'Status', 'Start Date',
                'Participants', 'Certificates', 'Certificate Coverage %',
                'Sessions', 'Attendance Rate %',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['location'],
                    $row['organizer'],
                    $row['status'],
                    $row['start_date'] ? $row['start_date']->format('Y-m-d') : '',
                    $row['participants'],
                    $row['certificates'],
                    $row['coverage'],
                    $row['sessions'],
                    $row['attendance'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
