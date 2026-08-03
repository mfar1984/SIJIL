<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\PwaParticipant;
use App\Support\PwaLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analytics for the PWA (mobile app) side of the system.
 *
 * The previous version reported system-wide totals (all events, all
 * certificates, all attendance) which had nothing to do with the app, and drew
 * its charts from event_pwa_participant - a pivot table that is empty, so every
 * chart rendered "no data". Everything here is now measured against the PWA
 * accounts themselves and the email match the mobile API actually uses.
 */
class PwaAnalyticsController extends Controller
{
    /** Ranges offered in the filter, in days. */
    public const RANGES = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 90 days',
        '365' => 'Last 12 months',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$this->requiredTablesExist()) {
            return view('ecertificate.analytics', $this->emptyPayload($request) + ['tablesExist' => false]);
        }

        [$dateRange, $start, $end] = $this->resolveRange($request);

        $eventIds = PwaLink::ownedEventIds($user);
        $events = Event::query()
            ->when($eventIds !== null, fn ($q) => $q->whereIn('id', $eventIds))
            ->orderByDesc('start_date')
            ->get(['id', 'name']);

        $selectedEventId = $request->filled('event_id') ? (int) $request->get('event_id') : null;

        if ($selectedEventId && !$events->contains('id', $selectedEventId)) {
            $selectedEventId = null;
        }

        $summary = $this->summary($user, $selectedEventId, $eventIds, $start, $end);

        return view('ecertificate.analytics', [
            'tablesExist' => true,

            'summary' => $summary,
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'selectedEventName' => $selectedEventId
                ? ($events->firstWhere('id', $selectedEventId)->name ?? null)
                : null,

            'demographics' => $this->demographics($user, $selectedEventId),

            'accountsByMonth' => $this->accountsByMonth($user, $selectedEventId),
            'dailyActivity' => $this->dailyActivity($user, $selectedEventId, $start, $end),
            'eventReach' => $this->eventReach($user, $eventIds),
            'recentActivity' => $this->recentActivity($user, $selectedEventId),

            'ranges' => self::RANGES,
            'dateRange' => $dateRange,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ]);
    }

    /**
     * Headline numbers. Every one of these is about the app, not the system.
     */
    protected function summary(
        $user,
        ?int $selectedEventId,
        ?array $eventIds,
        ?\Carbon\Carbon $start = null,
        ?\Carbon\Carbon $end = null
    ): array {
        $base = fn () => $this->accountQuery($user, $selectedEventId);

        $total = $base()->count();
        $signedIn = $base()->whereNotNull('last_login_at')->count();
        $changedPassword = $base()->whereNotNull('password_changed_at')->count();
        $inactive = $base()->where(function ($q) {
            $q->where('is_active', false)->orWhere('status', '!=', 'active');
        })->count();

        $linked = PwaLink::whereLinkedToParticipant(
            $base(),
            $selectedEventId ? [$selectedEventId] : $eventIds
        )->count();

        // The date filter used to reach only the activity chart, so every headline
        // figure was a lifetime total sitting next to a date picker that appeared to
        // do nothing. These two answer the range; the rest are labelled as all-time
        // in the view, because "how many accounts exist" is the more useful number
        // and narrowing it would hide most of them.
        $createdInRange = ($start && $end)
            ? $base()->whereBetween('pwa_participants.created_at', [$start, $end])->count()
            : $total;

        $signedInRange = ($start && $end)
            ? $base()->whereBetween('pwa_participants.last_login_at', [$start, $end])->count()
            : $signedIn;

        return [
            'total' => $total,
            'signed_in' => $signedIn,
            'created_in_range' => $createdInRange,
            'signed_in_range' => $signedInRange,
            'never_signed_in' => max(0, $total - $signedIn),
            'changed_password' => $changedPassword,
            'inactive' => $inactive,
            'linked' => $linked,
            'unlinked' => max(0, $total - $linked),
            'certificates_reachable' => $this->certificatesReachable($user, $selectedEventId, $eventIds),
            'signed_in_percent' => $total > 0 ? round($signedIn / $total * 100) : 0,
            'linked_percent' => $total > 0 ? round($linked / $total * 100) : 0,
        ];
    }

    /**
     * How many issued certificates an app account can actually reach. This is
     * the number that matters: an account with no matching participant row sees
     * an empty certificate list.
     */
    protected function certificatesReachable($user, ?int $selectedEventId, ?array $eventIds): int
    {
        $emails = $this->accountQuery($user, $selectedEventId)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => strtolower(trim($e)))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return 0;
        }

        return Certificate::query()
            ->whereIn('participant_id', function ($sub) use ($emails, $selectedEventId, $eventIds) {
                $sub->select('id')
                    ->from('participants')
                    ->whereNull('deleted_at')
                    ->whereIn(DB::raw('LOWER(email)'), $emails->all());

                if ($selectedEventId) {
                    $sub->where('event_id', $selectedEventId);
                } elseif ($eventIds !== null) {
                    $sub->whereIn('event_id', $eventIds);
                }
            })
            ->count();
    }

    /**
     * Gender, race and age spread of the app's account holders.
     *
     * These are optional profile fields, so "Not stated" is expected to be the
     * biggest slice until participants fill their profile in.
     */
    protected function demographics($user, ?int $selectedEventId): array
    {
        $total = $this->accountQuery($user, $selectedEventId)->count();

        $gender = $this->tally($user, $selectedEventId, 'gender', [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
        ]);

        $race = $this->tally($user, $selectedEventId, 'race');

        // Age bands, computed from date_of_birth.
        $ages = $this->accountQuery($user, $selectedEventId)
            ->whereNotNull('pwa_participants.date_of_birth')
            ->selectRaw("
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25 THEN '18-24'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 35 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 45 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 55 THEN '45-54'
                    ELSE '55+'
                END as band,
                COUNT(*) as c
            ")
            ->groupBy('band')
            ->pluck('c', 'band');

        $order = ['Under 18', '18-24', '25-34', '35-44', '45-54', '55+'];
        $ageBands = collect($order)
            ->map(fn ($band) => ['label' => $band, 'count' => (int) ($ages[$band] ?? 0)])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();

        $withDob = (int) $ages->sum();

        return [
            'total' => $total,
            'gender' => $gender,
            'race' => $race,
            'age_bands' => $ageBands,
            'with_date_of_birth' => $withDob,
            'without_date_of_birth' => max(0, $total - $withDob),
        ];
    }

    /**
     * Count accounts grouped by one nullable profile column, with blanks folded
     * into a single "Not stated" row at the end.
     *
     * @param  array<string, string>  $labels  optional value => display name map
     */
    protected function tally($user, ?int $selectedEventId, string $column, array $labels = [])
    {
        $rows = $this->accountQuery($user, $selectedEventId)
            ->selectRaw("COALESCE(NULLIF(TRIM($column), ''), '__blank__') as v, COUNT(*) as c")
            ->groupBy('v')
            ->pluck('c', 'v');

        $blank = (int) ($rows['__blank__'] ?? 0);
        $total = (int) $rows->sum();

        $stated = $rows
            ->reject(fn ($c, $v) => $v === '__blank__')
            ->map(fn ($c, $v) => [
                'label' => $labels[$v] ?? ucfirst((string) $v),
                'count' => (int) $c,
                'percent' => $total > 0 ? round($c / $total * 100) : 0,
            ])
            ->sortByDesc('count')
            ->values();

        if ($blank > 0) {
            $stated->push([
                'label' => 'Not stated',
                'count' => $blank,
                'percent' => $total > 0 ? round($blank / $total * 100) : 0,
                'blank' => true,
            ]);
        }

        return $stated;
    }

    /**
     * New app accounts per month for the last 12 months, oldest first so the
     * line reads left to right.
     */
    protected function accountsByMonth($user, ?int $selectedEventId)
    {
        $since = now()->copy()->startOfMonth()->subMonths(11);

        $rows = $this->accountQuery($user, $selectedEventId)
            ->where('pwa_participants.created_at', '>=', $since)
            ->selectRaw('YEAR(pwa_participants.created_at) as y, MONTH(pwa_participants.created_at) as m, COUNT(*) as c')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($r) => sprintf('%04d-%02d', $r->y, $r->m));

        $series = collect();

        for ($i = 0; $i < 12; $i++) {
            $month = $since->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $series->push([
                'label' => $month->format('M Y'),
                'count' => (int) ($rows->get($key)->c ?? 0),
            ]);
        }

        return $series;
    }

    /**
     * Day-by-day activity across the selected range.
     *
     * "New accounts" is when the account row was created. "Last sign-in" counts
     * accounts whose most recent sign-in landed on that day - the app does not
     * keep a sign-in history, only the latest timestamp, so this is the honest
     * label for it.
     */
    protected function dailyActivity($user, ?int $selectedEventId, Carbon $start, Carbon $end)
    {
        $created = $this->accountQuery($user, $selectedEventId)
            ->whereBetween('pwa_participants.created_at', [$start, $end])
            ->selectRaw('DATE(pwa_participants.created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $signIns = $this->accountQuery($user, $selectedEventId)
            ->whereBetween('pwa_participants.last_login_at', [$start, $end])
            ->selectRaw('DATE(pwa_participants.last_login_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $series = collect();
        $cursor = $start->copy()->startOfDay();
        $guard = 0;

        while ($cursor <= $end && $guard++ < 400) {
            $key = $cursor->format('Y-m-d');

            $series->push([
                'date' => $key,
                'label' => $cursor->format('j M'),
                'new_accounts' => (int) ($created[$key] ?? 0),
                'sign_ins' => (int) ($signIns[$key] ?? 0),
            ]);

            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Events ranked by how many app accounts can reach them, counted through
     * the email match rather than the empty pivot.
     */
    protected function eventReach($user, ?array $eventIds)
    {
        return Event::query()
            ->when($eventIds !== null, fn ($q) => $q->whereIn('events.id', $eventIds))
            ->select('events.id', 'events.name', 'events.start_date')
            ->selectRaw('(
                SELECT COUNT(DISTINCT p.id)
                FROM participants p
                WHERE p.event_id = events.id
                  AND p.deleted_at IS NULL
            ) as participant_count')
            ->selectRaw('(
                SELECT COUNT(DISTINCT pp.id)
                FROM pwa_participants pp
                JOIN participants p2
                  ON LOWER(p2.email) = LOWER(pp.email)
                WHERE p2.event_id = events.id
                  AND p2.deleted_at IS NULL
                  AND pp.deleted_at IS NULL
            ) as app_account_count')
            ->orderByDesc('app_account_count')
            ->orderByDesc('events.start_date')
            ->limit(8)
            ->get()
            ->filter(fn ($e) => $e->participant_count > 0)
            ->map(function ($event) {
                $coverage = $event->participant_count > 0
                    ? round($event->app_account_count / $event->participant_count * 100)
                    : 0;

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'participants' => (int) $event->participant_count,
                    'accounts' => (int) $event->app_account_count,
                    'coverage' => $coverage,
                ];
            })
            ->values();
    }

    /**
     * A short feed of things that happened on the app side.
     */
    protected function recentActivity($user, ?int $selectedEventId)
    {
        $created = $this->accountQuery($user, $selectedEventId)
            ->orderByDesc('pwa_participants.created_at')
            ->limit(8)
            ->get(['pwa_participants.id', 'pwa_participants.name', 'pwa_participants.email', 'pwa_participants.created_at'])
            ->map(fn ($p) => [
                'type' => 'account',
                'icon' => 'person_add',
                'title' => 'Account created',
                'detail' => $p->name . ' (' . $p->email . ')',
                'at' => $p->created_at,
            ]);

        $signedIn = $this->accountQuery($user, $selectedEventId)
            ->whereNotNull('pwa_participants.last_login_at')
            ->orderByDesc('pwa_participants.last_login_at')
            ->limit(8)
            ->get(['pwa_participants.id', 'pwa_participants.name', 'pwa_participants.email', 'pwa_participants.last_login_at'])
            ->map(fn ($p) => [
                'type' => 'sign_in',
                'icon' => 'login',
                'title' => 'Signed in',
                'detail' => $p->name . ' (' . $p->email . ')',
                'at' => $p->last_login_at,
            ]);

        return $created->merge($signedIn)
            ->filter(fn ($row) => $row['at'] !== null)
            ->sortByDesc('at')
            ->take(12)
            ->values();
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        [$dateRange, $start, $end] = $this->resolveRange($request);

        $eventIds = PwaLink::ownedEventIds($user);
        $selectedEventId = $request->filled('event_id') ? (int) $request->get('event_id') : null;

        $summary = $this->summary($user, $selectedEventId, $eventIds);
        $demographics = $this->demographics($user, $selectedEventId);
        $byMonth = $this->accountsByMonth($user, $selectedEventId);
        $daily = $this->dailyActivity($user, $selectedEventId, $start, $end);
        $reach = $this->eventReach($user, $eventIds);

        $filename = 'pwa_analytics_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($summary, $demographics, $byMonth, $daily, $reach, $start, $end) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Section', 'Label', 'Value', 'Extra']);

            fputcsv($out, ['Summary', 'App accounts', $summary['total'], '']);
            fputcsv($out, ['Summary', 'Signed in at least once', $summary['signed_in'], $summary['signed_in_percent'] . '%']);
            fputcsv($out, ['Summary', 'Never signed in', $summary['never_signed_in'], '']);
            fputcsv($out, ['Summary', 'Changed their password', $summary['changed_password'], '']);
            fputcsv($out, ['Summary', 'Matched to a participant record', $summary['linked'], $summary['linked_percent'] . '%']);
            fputcsv($out, ['Summary', 'Not matched to any participant', $summary['unlinked'], '']);
            fputcsv($out, ['Summary', 'Certificates reachable in the app', $summary['certificates_reachable'], '']);
            fputcsv($out, ['Summary', 'Deactivated accounts', $summary['inactive'], '']);

            foreach ($demographics['gender'] as $row) {
                fputcsv($out, ['Gender', $row['label'], $row['count'], $row['percent'] . '%']);
            }

            foreach ($demographics['race'] as $row) {
                fputcsv($out, ['Race', $row['label'], $row['count'], $row['percent'] . '%']);
            }

            foreach ($demographics['age_bands'] as $row) {
                fputcsv($out, ['Age band', $row['label'], $row['count'], '']);
            }

            fputcsv($out, ['Age band', 'Date of birth not stated', $demographics['without_date_of_birth'], '']);

            foreach ($byMonth as $row) {
                fputcsv($out, ['New accounts by month', $row['label'], $row['count'], '']);
            }

            foreach ($daily as $row) {
                fputcsv($out, ['Daily activity', $row['date'], $row['new_accounts'], $row['sign_ins']]);
            }

            foreach ($reach as $row) {
                fputcsv($out, [
                    'Event reach',
                    $row['name'],
                    $row['accounts'],
                    $row['participants'] . ' participant' . ($row['participants'] === 1 ? '' : 's')
                        . ' / ' . $row['coverage'] . '% coverage',
                ]);
            }

            fputcsv($out, ['Range', 'From', $start->format('Y-m-d'), '']);
            fputcsv($out, ['Range', 'To', $end->format('Y-m-d'), '']);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Base query: accounts this user may see, optionally narrowed to one event.
     */
    protected function accountQuery($user, ?int $selectedEventId)
    {
        $query = PwaLink::accountsFor($user);

        if ($selectedEventId) {
            $query = PwaLink::scopeToEvent($query, $selectedEventId);
        }

        return $query;
    }

    /**
     * @return array{0: string, 1: Carbon, 2: Carbon}
     */
    protected function resolveRange(Request $request): array
    {
        $range = (string) $request->get('date_range', '30');

        if ($range === 'custom') {
            $start = Carbon::parse($request->get('start_date', now()->subDays(29)->format('Y-m-d')))->startOfDay();
            $end = Carbon::parse($request->get('end_date', now()->format('Y-m-d')))->endOfDay();

            if ($start > $end) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            // Keep the chart readable and the loop bounded.
            if ($start->diffInDays($end) > 365) {
                $start = $end->copy()->subDays(365)->startOfDay();
            }

            return ['custom', $start, $end];
        }

        if (!array_key_exists($range, self::RANGES)) {
            $range = '30';
        }

        $days = (int) $range;

        return [$range, now()->copy()->subDays($days - 1)->startOfDay(), now()->copy()->endOfDay()];
    }

    protected function requiredTablesExist(): bool
    {
        foreach (['pwa_participants', 'participants', 'events', 'certificates'] as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    protected function emptyPayload(Request $request): array
    {
        return [
            'summary' => [
                'total' => 0, 'signed_in' => 0, 'never_signed_in' => 0,
                'created_in_range' => 0, 'signed_in_range' => 0,
                'changed_password' => 0, 'inactive' => 0, 'linked' => 0,
                'unlinked' => 0, 'certificates_reachable' => 0,
                'signed_in_percent' => 0, 'linked_percent' => 0,
            ],
            'demographics' => [
                'total' => 0,
                'gender' => collect(),
                'race' => collect(),
                'age_bands' => collect(),
                'with_date_of_birth' => 0,
                'without_date_of_birth' => 0,
            ],
            'events' => collect(),
            'selectedEventId' => null,
            'selectedEventName' => null,
            'accountsByMonth' => collect(),
            'dailyActivity' => collect(),
            'eventReach' => collect(),
            'recentActivity' => collect(),
            'ranges' => self::RANGES,
            'dateRange' => '30',
            'startDate' => now()->subDays(29)->format('Y-m-d'),
            'endDate' => now()->format('Y-m-d'),
        ];
    }
}
