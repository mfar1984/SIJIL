<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class SecurityAuditController extends Controller
{
    /**
     * The tabs, and how each one narrows the audit trail.
     *
     * Kept in one place because the tab label, the count beside it and the rows it
     * shows must all come from the same condition. They previously did not: the
     * tabs rendered four separate unfiltered ->get() collections while the footer
     * and pager described a fifth, paginated query that no tab ever displayed. A
     * tab holding one row sat above "Showing 1 to 10 of 44 entries" across five
     * pages, and none of the filters changed anything on screen because they were
     * only ever applied to that unseen query.
     */
    private const TABS = [
        'all' => 'All events',
        'auth' => 'Sign-in activity',
        'role' => 'Roles and permissions',
        'user' => 'User accounts',
    ];

    /**
     * Display the security audit page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tab = array_key_exists($request->get('tab'), self::TABS) ? $request->get('tab') : 'all';
        $perPage = max(5, min(100, (int) \App\Support\SystemSettings::perPage($request, 10)));

        // One builder, rebuilt per use so the filters can never drift apart.
        $base = fn () => $this->scopedQuery($request);

        $activities = $this->applyTab($base(), $tab)
            ->with(['causer', 'subject'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        // Counts come from the same filtered query as the rows, so a tab can no
        // longer advertise a number it does not go on to show.
        $tabCounts = [];

        foreach (array_keys(self::TABS) as $key) {
            $tabCounts[$key] = $this->applyTab($base(), $key)->count();
        }

        return view('settings.security-audit', [
            'activities' => $activities,
            'tabs' => self::TABS,
            'tab' => $tab,
            'tabCounts' => $tabCounts,
            'perPage' => $perPage,

            // Dropdown options taken from the rows in scope, so the filter cannot
            // offer a value that matches nothing.
            'logNames' => $this->scopedQuery($request, false)->distinct()->pluck('log_name')->filter()->sort()->values(),
            'events' => $this->scopedQuery($request, false)->distinct()->pluck('event')->filter()->sort()->values(),

            'stats' => $this->stats($request),
        ]);
    }

    /**
     * Everything the audit trail considers security related.
     *
     * The old page had two competing definitions of this: the statistic counted
     * four log names and reported 30, while the pager also matched on description
     * and reported 44, side by side on the same screen. This is the only
     * definition now.
     *
     * @param  bool  $withFilters  false gives the unfiltered scope, for building
     *                             the filter dropdowns themselves.
     */
    private function scopedQuery(Request $request, bool $withFilters = true)
    {
        $query = Activity::query()->where(function ($q) {
            $q->whereIn('log_name', ['auth', 'security', 'user', 'role'])
                ->orWhere('description', 'LIKE', '%login%')
                ->orWhere('description', 'LIKE', '%logout%')
                ->orWhere('description', 'LIKE', '%logged in%')
                ->orWhere('description', 'LIKE', '%logged out%')
                ->orWhere('description', 'LIKE', '%password%')
                ->orWhere('description', 'LIKE', '%permission%')
                ->orWhere('description', 'LIKE', '%role%')
                ->orWhere('description', 'LIKE', '%user%');
        });

        if (! $withFilters) {
            return $query;
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                    ->orWhere('log_name', 'LIKE', "%{$search}%")
                    ->orWhere('event', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('severity')) {
            $query->where(function ($q) use ($request) {
                match ($request->severity) {
                    'high' => $q->where('description', 'LIKE', '%failed%')
                        ->orWhere('description', 'LIKE', '%unauthorized%')
                        ->orWhere('description', 'LIKE', '%suspicious%')
                        ->orWhere('description', 'LIKE', '%banned%'),
                    'medium' => $q->where('description', 'LIKE', '%password%')
                        ->orWhere('description', 'LIKE', '%role%')
                        ->orWhere('description', 'LIKE', '%permission%'),
                    'low' => $q->where('description', 'LIKE', '%logged in%')
                        ->orWhere('description', 'LIKE', '%logged out%'),
                    default => $q,
                };
            });
        }

        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();

            // These looked forward from today, so "last 7 days" selected the next
            // seven and matched rows that cannot exist yet.
            match ($request->date_filter) {
                'today' => $query->whereDate('created_at', $today->toDateString()),
                'week' => $query->where('created_at', '>=', $today->copy()->subDays(6)),
                'month' => $query->where('created_at', '>=', $today->copy()->subDays(29)),
                'past' => $query->where('created_at', '<', $today),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Narrow a query to one tab.
     *
     * User accounts matches on description as well as log name: nothing writes a
     * 'user' log name on this installation, so that tab counted zero while the
     * user records it was meant to show sat under the default log.
     */
    private function applyTab($query, string $tab)
    {
        return match ($tab) {
            'auth' => $query->where('log_name', 'auth'),
            'role' => $query->where(function ($q) {
                $q->where('log_name', 'role')
                    ->orWhere('description', 'LIKE', '%role%')
                    ->orWhere('description', 'LIKE', '%permission%');
            }),
            'user' => $query->where(function ($q) {
                $q->where('log_name', 'user')
                    ->orWhere('description', 'LIKE', '%user%');
            }),
            default => $query,
        };
    }

    /**
     * The cards along the top, all measured within the same scope and honouring
     * the same filters as the table. They used to search every row in the log and
     * ignore the filters entirely.
     */
    private function stats(Request $request): array
    {
        $base = fn () => $this->scopedQuery($request);

        return [
            'total' => $base()->count(),
            'sign_ins' => $base()->where('description', 'LIKE', '%logged in%')->count(),
            'failed' => (clone $base())->where(function ($q) {
                $q->where('description', 'LIKE', '%failed%')
                    ->orWhere('description', 'LIKE', '%unauthorized%')
                    ->orWhere('description', 'LIKE', '%suspicious%');
            })->count(),
            'role_changes' => $base()->where(function ($q) {
                $q->where('log_name', 'role')
                    ->orWhere('description', 'LIKE', '%permission%');
            })->count(),
        ];
    }

    /**
     * Show detailed information about a specific security activity.
     *
     * @param Activity $activity
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDetails(Activity $activity)
    {
        // Determine the category based on log_name
        $category = match($activity->log_name) {
            'auth' => 'Authentication',
            'security' => 'Security Alert',
            'user' => 'User Management',
            'role' => 'Role Management',
            default => ucfirst($activity->log_name ?: 'General')
        };

        // Determine status based on description
        $status = 'Success';
        if (str_contains(strtolower($activity->description), 'failed') || 
            str_contains(strtolower($activity->description), 'unauthorized') || 
            str_contains(strtolower($activity->description), 'suspicious')) {
            $status = 'Failed';
        }

        // Generate security data based on activity
        $securityData = [
            'activity_id' => $activity->id,
            'log_name' => $activity->log_name,
            'event' => $activity->event,
            'causer_id' => $activity->causer_id,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'properties' => $activity->properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => $activity->created_at->setTimezone('Asia/Kuala_Lumpur')->format('l - j F Y, h:i:s A') . ' GMT +8',
            'browser' => 'Chrome', // You can implement browser detection
            'os' => 'Windows', // You can implement OS detection
            'session_id' => 'sess_' . uniqid(),
            'auth_method' => 'password',
            '2fa_used' => false
        ];

        return response()->json([
            'id' => $activity->id,
            'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
            'user' => $activity->causer ? $activity->causer->email : 'System',
            'ip_address' => request()->ip(),
            'event' => $activity->description,
            'category' => $category,
            'status' => $status,
            'user_agent' => request()->userAgent(),
            'description' => $activity->description,
            'data' => $securityData
        ]);
    }

    /**
     * Clear security audit logs based on specified criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSecurityLogs(Request $request)
    {
        try {
            $days = $request->input('days', 'all');
            
            // Focus on security-related activities
            $query = Activity::where(function($q) {
                $q->where('log_name', 'auth')
                  ->orWhere('log_name', 'security')
                  ->orWhere('log_name', 'user')
                  ->orWhere('log_name', 'role')
                  ->orWhere('description', 'LIKE', '%login%')
                  ->orWhere('description', 'LIKE', '%logout%')
                  ->orWhere('description', 'LIKE', '%password%')
                  ->orWhere('description', 'LIKE', '%permission%')
                  ->orWhere('description', 'LIKE', '%role%')
                  ->orWhere('description', 'LIKE', '%user%');
            });
            
            if ($days === 'all') {
                // Clear all security logs
                $deletedCount = $query->count();
                $query->delete();
                $message = "All {$deletedCount} security audit logs have been cleared successfully.";
            } else {
                // Clear security logs older than specified days
                $cutoffDate = now()->subDays((int) $days);
                $deletedCount = $query->where('created_at', '<', $cutoffDate)->count();
                $query->where('created_at', '<', $cutoffDate)->delete();
                $message = "{$deletedCount} security audit logs older than {$days} days have been cleared successfully.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear security logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
