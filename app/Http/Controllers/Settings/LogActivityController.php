<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class LogActivityController extends Controller
{
    /**
     * Display the log activity page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

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
        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();
        
        // Get unique events for filter dropdown
        $events = Activity::distinct()->pluck('event')->filter()->values();

        return view('settings.log-activity', [
            'activities' => $activities,
            'logNames' => $logNames,
            'events' => $events
        ]);
    }

    /**
     * Show detailed information about a specific activity log.
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
            'event' => 'Event Management',
            'certificate' => 'Certificate Management',
            'campaign' => 'Campaign Management',
            'attendance' => 'Attendance Management',
            'system' => 'System Activity',
            default => ucfirst($activity->log_name ?: 'General')
        };

        // Determine status based on description and event
        $status = 'Success';
        if (str_contains(strtolower($activity->description), 'failed') || 
            str_contains(strtolower($activity->description), 'unauthorized') || 
            str_contains(strtolower($activity->description), 'suspicious') ||
            str_contains(strtolower($activity->description), 'error') ||
            $activity->event === 'deleted') {
            $status = 'Failed';
        }

        // Generate activity data based on activity
        $activityData = [
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
            'login_method' => 'password',
            'additional_info' => [
                'batch_uuid' => $activity->batch_uuid,
                'created_at' => $activity->created_at->setTimezone('Asia/Kuala_Lumpur')->format('l - j F Y, h:i:s A') . ' GMT +8',
                'updated_at' => $activity->updated_at->setTimezone('Asia/Kuala_Lumpur')->format('l - j F Y, h:i:s A') . ' GMT +8',
            ]
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
            'log_name' => $activity->log_name,
            'event_type' => $activity->event,
            'data' => $activityData
        ]);
    }

    /**
     * Clear activity logs based on specified criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearLogs(Request $request)
    {
        try {
            $days = $request->input('days', 'all');
            
            if ($days === 'all') {
                // Clear all logs
                $deletedCount = Activity::count();
                Activity::truncate();
                $message = "All {$deletedCount} activity logs have been cleared successfully.";
            } else {
                // Clear logs older than specified days
                $cutoffDate = now()->subDays((int) $days);
                $deletedCount = Activity::where('created_at', '<', $cutoffDate)->count();
                Activity::where('created_at', '<', $cutoffDate)->delete();
                $message = "{$deletedCount} activity logs older than {$days} days have been cleared successfully.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
