<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\Participant;
use App\Models\PwaParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The dashboard.
 *
 * Rewritten. The version this replaces had eighteen charts, and most of them
 * could not tell the truth:
 *
 *   - Age was computed with Carbon::now()->diffInYears($dob), which is signed on
 *     Carbon 3 and returns a negative number, so every participant with a date of
 *     birth was filed under "Under 18".
 *   - The five trend badges cloned a builder that already carried a date range and
 *     then added the preceding range on top, two conditions no row can satisfy, so
 *     every comparison was 0%.
 *   - Five charts drew from attendance_records and campaigns, both of which hold no
 *     rows at all, and one hardcoded its "bounced" figure to zero.
 *   - Event categories were a literal placeholder; the column does not exist.
 *   - Gender and status charts fell back to ['No Data' => 1], inventing a slice.
 *   - The summary counted events matching created_at OR start_date while the tables
 *     below counted only created_at, so the two halves of one page described
 *     different sets of events.
 *
 * What is here instead follows the approach that makes the Event Statistics report
 * correct: resolve one set of events for the filter, then measure everything
 * against that set. Nothing is charted unless the data behind it exists, and where
 * a feature is set up but unused the page says so in words rather than drawing a
 * flat line at zero.
 */
class DashboardController extends Controller
{
    /**
     * Ranges offered by the filter, in days back from today. Mirrors the Event
     * Statistics report so the two pages agree about what "last 90 days" means.
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
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');

        [$start, $end, $rangeLabel] = $this->resolveRange($request);

        $eventQuery = $this->eventQuery($request, $start, $end, $isAdmin);
        $eventIds = (clone $eventQuery)->pluck('id')->all();

        $totals = $this->totalsFor($eventIds);

        // The same measurements over the window immediately before this one. Built
        // from its own query rather than by narrowing the current one, which is
        // what made the old comparison structurally zero.
        $previous = null;

        if ($start && $end) {
            $length = $start->diffInDays($end) + 1;
            $prevEnd = (clone $start)->subDay();
            $prevStart = (clone $prevEnd)->subDays($length - 1);

            $prevIds = $this->eventQuery($request, $prevStart, $prevEnd, $isAdmin)->pluck('id')->all();
            $previous = $this->totalsFor($prevIds);
            $previous['range'] = $prevStart->format('j M Y') . ' – ' . $prevEnd->format('j M Y');
        }

        return view('dashboard', [
            'isAdmin' => $isAdmin,
            'rangeLabel' => $rangeLabel,
            'dateFilter' => $request->get('date_filter', 'last_year'),
            'totals' => $totals,
            'previous' => $previous,
            'changes' => $this->changes($totals, $previous),

            'registrationSeries' => $this->monthlySeries(
                Participant::whereIn('event_id', $eventIds),
                'COALESCE(registration_date, created_at)'
            ),
            'certificateSeries' => $this->monthlySeries(
                Certificate::whereIn('event_id', $eventIds)->whereNotNull('generated_at'),
                'generated_at'
            ),

            'demographics' => $this->demographics($eventIds),
            'ageGroups' => $this->ageGroups($eventIds),
            'topEvents' => $this->topEvents($eventIds),
            'coverageByEvent' => $this->coverageByEvent($eventIds),
            'byOrganizer' => $isAdmin ? $this->byOrganizer($eventIds) : collect(),
            'appAccounts' => $this->appAccounts($eventIds, $isAdmin, $user),
            'upcoming' => $this->upcoming($isAdmin),
            'recentRegistrations' => $this->recentRegistrations($eventIds),

            'organizers' => $isAdmin
                ? User::whereIn('id', Event::distinct()->pluck('user_id'))->orderBy('name')->get(['id', 'name'])
                : collect(),
            'statuses' => Event::whereIn('id', $eventIds)->distinct()->pluck('status')->filter()->sort()->values(),
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
            return [null, null, 'all time'];
        }

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        return [$start, $end, $start->format('j M Y') . ' – ' . $end->format('j M Y')];
    }

    /**
     * Events in scope.
     *
     * Filtered on start_date alone. The old page used created_at OR start_date for
     * the summary and created_at for the tables, so one filter produced two
     * different answers on the same screen.
     */
    private function eventQuery(Request $request, ?Carbon $start, ?Carbon $end, bool $isAdmin)
    {
        $query = Event::query();

        if (! $isAdmin) {
            $query->where('user_id', Auth::id());
        } elseif ($request->filled('organizer')) {
            $query->where('user_id', $request->organizer);
        }

        if ($start && $end) {
            $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        return $query;
    }

    /**
     * Headline figures for a set of events, in a fixed number of queries however
     * many events there are.
     */
    private function totalsFor(array $eventIds): array
    {
        if (! $eventIds) {
            return [
                'events' => 0, 'participants' => 0, 'certificates' => 0,
                'checked_in' => 0, 'sessions' => 0,
                'attendance_rate' => 0.0, 'coverage_rate' => 0.0,
            ];
        }

        $participants = Participant::whereIn('event_id', $eventIds)->count();
        $certificates = Certificate::whereIn('event_id', $eventIds)->count();

        $sessionIds = DB::table('attendance_sessions')
            ->join('attendances', 'attendance_sessions.attendance_id', '=', 'attendances.id')
            ->whereIn('attendances.event_id', $eventIds)
            ->pluck('attendance_sessions.id');

        // Kept as a real measurement even though no scan has been recorded yet:
        // the view says so in words instead of drawing an empty chart.
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
            'attendance_rate' => $participants > 0 ? round($checkedIn / $participants * 100, 1) : 0.0,
            'coverage_rate' => $participants > 0 ? round($certificates / $participants * 100, 1) : 0.0,
        ];
    }

    /**
     * Percentage change against the previous window.
     *
     * Returns null rather than 100 when there is nothing to compare against, so
     * the page can say "no earlier data" instead of implying growth.
     *
     * @return array<string, ?float>
     */
    private function changes(array $totals, ?array $previous): array
    {
        $keys = ['events', 'participants', 'certificates'];

        if (! $previous) {
            return array_fill_keys($keys, null);
        }

        $out = [];

        foreach ($keys as $key) {
            $was = $previous[$key] ?? 0;
            $out[$key] = $was > 0
                ? round((($totals[$key] - $was) / $was) * 100, 1)
                : null;
        }

        return $out;
    }

    /**
     * Counts per calendar month for a query, using whichever column records when
     * the thing actually happened.
     */
    private function monthlySeries($query, string $dateExpression)
    {
        return $query
            ->selectRaw("DATE_FORMAT({$dateExpression}, '%Y-%m') as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->bucket)->format('M Y'),
                'count' => (int) $row->total,
            ])
            ->values();
    }

    /**
     * Gender, race and registration type across these events.
     *
     * Blank and missing values are folded into one "Not stated" row and sorted
     * last, so a chart is never padded with an invented category.
     */
    private function demographics(array $eventIds): array
    {
        $tally = function (string $column) use ($eventIds) {
            if (! $eventIds) {
                return collect();
            }

            $rows = Participant::whereIn('event_id', $eventIds)
                ->selectRaw("COALESCE(NULLIF(TRIM({$column}), ''), '__blank__') as label, COUNT(*) as total")
                ->groupBy('label')
                ->get();

            $sum = max(1, (int) $rows->sum('total'));

            return $rows
                ->map(fn ($row) => [
                    'label' => $row->label === '__blank__' ? 'Not stated' : ucfirst($row->label),
                    'count' => (int) $row->total,
                    'percent' => round($row->total / $sum * 100, 1),
                    'blank' => $row->label === '__blank__',
                ])
                ->sortBy(fn ($row) => [$row['blank'] ? 1 : 0, -$row['count']])
                ->values();
        };

        return [
            'total' => $eventIds ? Participant::whereIn('event_id', $eventIds)->count() : 0,
            'gender' => $tally('gender'),
            'race' => $tally('race'),
            'type' => $tally('registration_type'),
        ];
    }

    /**
     * Age bands.
     *
     * Worked out in SQL with TIMESTAMPDIFF. The previous implementation loaded
     * every row and called Carbon::now()->diffInYears($dob), which on Carbon 3
     * returns a negative number, so the first branch matched everyone and all 371
     * participants with a date of birth were reported as being under 18.
     */
    private function ageGroups(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $bands = Participant::whereIn('event_id', $eventIds)
            ->whereNotNull('date_of_birth')
            ->selectRaw("
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 24 THEN '18-24'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 34 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 44 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 54 THEN '45-54'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= 64 THEN '55-64'
                    ELSE '65 and over'
                END as band,
                COUNT(*) as total
            ")
            ->groupBy('band')
            ->pluck('total', 'band');

        // A fixed order, so the bars read left to right by age rather than by size.
        $order = ['Under 18', '18-24', '25-34', '35-44', '45-54', '55-64', '65 and over'];

        return collect($order)
            ->map(fn ($band) => ['label' => $band, 'count' => (int) ($bands[$band] ?? 0)])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();
    }

    /**
     * The largest events in this range, by how many people registered.
     */
    private function topEvents(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $counts = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        return Event::whereIn('id', $eventIds)
            ->get(['id', 'name'])
            ->map(fn ($event) => [
                'label' => $event->name,
                'count' => (int) ($counts[$event->id] ?? 0),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->take(8)
            ->values();
    }

    /**
     * How much of each event's audience holds a certificate.
     */
    private function coverageByEvent(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $registered = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $issued = Certificate::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(DISTINCT participant_id) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        return Event::whereIn('id', $eventIds)
            ->get(['id', 'name'])
            ->map(function ($event) use ($registered, $issued) {
                $total = (int) ($registered[$event->id] ?? 0);
                $have = (int) ($issued[$event->id] ?? 0);

                return [
                    'label' => $event->name,
                    'registered' => $total,
                    'issued' => $have,
                    'percent' => $total > 0 ? round($have / $total * 100, 1) : 0.0,
                ];
            })
            ->filter(fn ($row) => $row['registered'] > 0)
            ->sortByDesc('percent')
            ->values();
    }

    /**
     * Administrators only: which organizer the activity belongs to. An organizer
     * looking at their own dashboard has nothing to compare against.
     */
    private function byOrganizer(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        $events = Event::whereIn('id', $eventIds)->get(['id', 'user_id']);

        $participants = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $owners = User::whereIn('id', $events->pluck('user_id')->unique())->pluck('name', 'id');

        return $events
            ->groupBy('user_id')
            ->map(fn ($group, $userId) => [
                'label' => $owners[$userId] ?? 'Unknown',
                'events' => $group->count(),
                'count' => (int) $group->sum(fn ($e) => $participants[$e->id] ?? 0),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values();
    }

    /**
     * App accounts reachable from these events.
     *
     * Counted by matching email, the way the mobile API resolves an account to its
     * certificates. Sign-in figures are reported as plain numbers rather than a
     * chart: only a couple of accounts have ever signed in, and a time series of
     * that would be a flat line pretending to be information.
     */
    private function appAccounts(array $eventIds, bool $isAdmin, $user): array
    {
        $emails = $eventIds
            ? Participant::whereIn('event_id', $eventIds)
                ->whereNotNull('email')
                ->selectRaw('LOWER(TRIM(email)) as email')
                ->distinct()
                ->pluck('email')
            : collect();

        $reachable = $emails->isEmpty() ? 0 : PwaParticipant::whereIn(
            DB::raw('LOWER(TRIM(email))'),
            $emails->all()
        )->count();

        $scope = \App\Support\PwaLink::accountsFor($isAdmin ? null : $user);

        return [
            'total' => (clone $scope)->count(),
            'signed_in' => (clone $scope)->whereNotNull('last_login_at')->count(),
            'reachable_from_range' => $reachable,
            'participants_with_email' => $emails->count(),
        ];
    }

    /**
     * The next few events that have not happened yet, regardless of the filter:
     * what is coming is useful on a dashboard even when the range looks backwards.
     */
    private function upcoming(bool $isAdmin)
    {
        return Event::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', Auth::id()))
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->take(5)
            ->get(['id', 'name', 'start_date', 'location', 'max_participants'])
            ->map(fn ($event) => [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date,
                'location' => $event->location,
                'registered' => Participant::where('event_id', $event->id)->count(),
                'capacity' => (int) $event->max_participants,
            ]);
    }

    /**
     * The most recent sign-ups in range, as a short list.
     */
    private function recentRegistrations(array $eventIds)
    {
        if (! $eventIds) {
            return collect();
        }

        return Participant::whereIn('event_id', $eventIds)
            ->with('event:id,name')
            ->orderByDesc('id')
            ->take(8)
            ->get(['id', 'name', 'email', 'event_id', 'registration_date', 'created_at', 'registration_type']);
    }
}
