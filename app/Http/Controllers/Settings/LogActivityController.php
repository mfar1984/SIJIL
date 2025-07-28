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
}
