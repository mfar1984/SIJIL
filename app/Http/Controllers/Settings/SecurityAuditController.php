<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class SecurityAuditController extends Controller
{
    /**
     * Display the security audit page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        // Focus on security-related activities
        $query->where(function($q) {
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

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('log_name', 'LIKE', "%{$search}%")
                  ->orWhere('event', 'LIKE', "%{$search}%");
            });
        }

        // Apply log name filter
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Apply event filter
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Apply severity filter
        if ($request->filled('severity')) {
            switch ($request->severity) {
                case 'high':
                    $query->where(function($q) {
                        $q->where('description', 'LIKE', '%failed login%')
                          ->orWhere('description', 'LIKE', '%unauthorized%')
                          ->orWhere('description', 'LIKE', '%suspicious%');
                    });
                    break;
                case 'medium':
                    $query->where(function($q) {
                        $q->where('description', 'LIKE', '%password change%')
                          ->orWhere('description', 'LIKE', '%role change%')
                          ->orWhere('description', 'LIKE', '%permission%');
                    });
                    break;
                case 'low':
                    $query->where(function($q) {
                        $q->where('description', 'LIKE', '%login%')
                          ->orWhere('description', 'LIKE', '%logout%')
                          ->where('description', 'NOT LIKE', '%failed%');
                    });
                    break;
            }
        }

        // Apply date filter
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('created_at', '<', $today->format('Y-m-d'));
                    break;
            }
        }

        // Get per_page parameter with default 10
        $perPage = $request->get('per_page', 10);

        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get unique log names for filter dropdown
        $logNames = Activity::where(function($q) {
            $q->where('log_name', 'auth')
              ->orWhere('log_name', 'security')
              ->orWhere('log_name', 'user')
              ->orWhere('log_name', 'role');
        })->distinct()->pluck('log_name')->filter()->values();
        
        // Get unique events for filter dropdown
        $events = Activity::where(function($q) {
            $q->where('log_name', 'auth')
              ->orWhere('log_name', 'security')
              ->orWhere('log_name', 'user')
              ->orWhere('log_name', 'role');
        })->distinct()->pluck('event')->filter()->values();

        // Get security statistics
        $totalSecurityEvents = Activity::where(function($q) {
            $q->where('log_name', 'auth')
              ->orWhere('log_name', 'security')
              ->orWhere('log_name', 'user')
              ->orWhere('log_name', 'role');
        })->count();

        $failedLogins = Activity::where('description', 'LIKE', '%failed login%')->count();
        $suspiciousActivities = Activity::where('description', 'LIKE', '%suspicious%')->count();
        $passwordChanges = Activity::where('description', 'LIKE', '%password change%')->count();

        // Get separate collections for each tab
        $userActivities = Activity::where('log_name', 'user')->orderBy('created_at', 'desc')->get();
        $roleActivities = Activity::where('log_name', 'role')->orderBy('created_at', 'desc')->get();
        $authActivities = Activity::where('log_name', 'auth')->orderBy('created_at', 'desc')->get();

        return view('settings.security-audit', [
            'activities' => $activities,
            'userActivities' => $userActivities,
            'roleActivities' => $roleActivities,
            'authActivities' => $authActivities,
            'logNames' => $logNames,
            'events' => $events,
            'totalSecurityEvents' => $totalSecurityEvents,
            'failedLogins' => $failedLogins,
            'suspiciousActivities' => $suspiciousActivities,
            'passwordChanges' => $passwordChanges
        ]);
    }
}
