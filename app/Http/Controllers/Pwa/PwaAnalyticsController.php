<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\PwaParticipant;
use App\Models\Event;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PwaAnalyticsController extends Controller
{
    /**
     * Display PWA analytics with role-based data filtering
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get date range filter
        $dateRange = $request->get('date_range', '7');
        $startDate = $request->get('start_date', now()->subDays(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Calculate date range
        if ($dateRange === 'custom') {
            $startDate = $request->get('start_date', now()->subDays(6)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
        } else {
            $days = (int) $dateRange;
            $startDate = now()->subDays($days - 1)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }
        
        // Check if required tables exist
        $tablesExist = $this->checkRequiredTables();
        
        if (!$tablesExist) {
            // Return view with empty data if tables don't exist
            return view('ecertificate.analytics', [
                'totalParticipants' => 0,
                'totalEvents' => 0,
                'totalAttendance' => 0,
                'totalCertificates' => 0,
                'participants' => 0,
                'attendance' => 0,
                'certificates' => 0,
                'events' => collect(),
                'selectedEventId' => null,
                'monthlyStats' => collect(),
                'topEvents' => collect(),
                'recentActivity' => collect(),
                'activityTrends' => collect(),
                'dateRange' => $dateRange,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'tablesExist' => false
            ]);
        }
        
        // Multi-tenant data filtering based on user role
        if ($user->hasRole('Administrator')) {
            // Administrator sees global analytics
            $totalParticipants = PwaParticipant::count();
            $totalEvents = Event::count();
            $totalAttendance = AttendanceRecord::count();
            $totalCertificates = Certificate::count();
            
            // Get events for filtering
            $events = Event::all();
            $selectedEventId = $request->get('event_id');
            
            if ($selectedEventId) {
                $participants = PwaParticipant::whereHas('events', function($q) use ($selectedEventId) {
                    $q->where('events.id', $selectedEventId);
                })->count();
                
                // Get attendance for specific event through attendance_sessions -> attendance -> event
                $eventSessions = AttendanceSession::whereHas('attendance', function($q) use ($selectedEventId) {
                        $q->where('event_id', $selectedEventId);
                    })
                    ->pluck('id');
                $attendance = AttendanceRecord::whereIn('attendance_session_id', $eventSessions)->count();
                
                $certificates = Certificate::where('event_id', $selectedEventId)->count();
            } else {
                $participants = $totalParticipants;
                $attendance = $totalAttendance;
                $certificates = $totalCertificates;
            }
            
            // Monthly statistics for all events
            $monthlyStats = $this->getMonthlyStats();
            $topEvents = $this->getTopPerformingEvents($user);
            $recentActivity = $this->getRecentActivity($user);
            $activityTrends = $this->getActivityTrends($user, $startDate, $endDate);
            
        } else {
            // Organizer sees only their own analytics
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            
            $totalParticipants = PwaParticipant::whereHas('events', function($q) use ($organizerEvents) {
                $q->whereIn('events.id', $organizerEvents);
            })->count();
            
            $totalEvents = Event::where('user_id', $user->id)->count();
            
            // Get attendance for organizer's events through attendance_sessions -> attendance -> event
            $organizerSessions = AttendanceSession::whereHas('attendance', function($q) use ($organizerEvents) {
                    $q->whereIn('event_id', $organizerEvents);
                })
                ->pluck('id');
            $totalAttendance = AttendanceRecord::whereIn('attendance_session_id', $organizerSessions)->count();
            
            $totalCertificates = Certificate::whereIn('event_id', $organizerEvents)->count();
            
            // Get events for filtering
            $events = Event::where('user_id', $user->id)->get();
            $selectedEventId = $request->get('event_id');
            
            if ($selectedEventId && $organizerEvents->contains($selectedEventId)) {
                $participants = PwaParticipant::whereHas('events', function($q) use ($selectedEventId) {
                    $q->where('events.id', $selectedEventId);
                })->count();
                
                // Get attendance for specific event
                $eventSessions = AttendanceSession::whereHas('attendance', function($q) use ($selectedEventId) {
                        $q->where('event_id', $selectedEventId);
                    })
                    ->pluck('id');
                $attendance = AttendanceRecord::whereIn('attendance_session_id', $eventSessions)->count();
                
                $certificates = Certificate::where('event_id', $selectedEventId)->count();
            } else {
                $participants = $totalParticipants;
                $attendance = $totalAttendance;
                $certificates = $totalCertificates;
            }
            
                    // Monthly statistics for organizer's events
        $monthlyStats = $this->getMonthlyStats($organizerEvents);
        $topEvents = $this->getTopPerformingEvents($user);
        $recentActivity = $this->getRecentActivity($user);
        $activityTrends = $this->getActivityTrends($user, $startDate, $endDate);
        }
        
        return view('ecertificate.analytics', compact(
            'totalParticipants',
            'totalEvents', 
            'totalAttendance',
            'totalCertificates',
            'participants',
            'attendance',
            'certificates',
            'events',
            'selectedEventId',
            'monthlyStats',
            'topEvents',
            'recentActivity',
            'activityTrends',
            'dateRange',
            'startDate',
            'endDate',
            'tablesExist'
        ));
    }

    /**
     * Check if required tables exist
     */
    private function checkRequiredTables()
    {
        $requiredTables = ['pwa_participants', 'events', 'attendance_records', 'certificates'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $eventIds = null;
        
        if (!$user->hasRole('Administrator')) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
        }
        
        $data = $this->getExportData($eventIds);
        
        $filename = 'pwa_analytics_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Metric', 'Value', 'Date']);
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get monthly statistics
     */
    private function getMonthlyStats($eventIds = null)
    {
        $query = DB::table('pwa_participants as pp')
            ->selectRaw('YEAR(pp.created_at) as year, MONTH(pp.created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12);
            
        if ($eventIds) {
            // For organizer view, we need to join with events
            $query->join('event_pwa_participant as ep', 'pp.id', '=', 'ep.pwa_participant_id')
                  ->whereIn('ep.event_id', $eventIds);
        }
        
        return $query->get()->map(function($item) {
            return [
                'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                'count' => $item->count
            ];
        });
    }

    /**
     * Get top performing events
     */
    private function getTopPerformingEvents($user)
    {
        $query = Event::select('events.*')
            ->selectRaw('COUNT(DISTINCT ep.pwa_participant_id) as participant_count')
            ->leftJoin('event_pwa_participant as ep', 'events.id', '=', 'ep.event_id')
            ->groupBy('events.id', 'events.name', 'events.start_date', 'events.end_date', 'events.location', 'events.user_id', 'events.created_at', 'events.updated_at')
            ->orderBy('participant_count', 'desc')
            ->limit(5);
            
        if (!$user->hasRole('Administrator')) {
            $query->where('events.user_id', $user->id);
        }
        
        return $query->get();
    }

    /**
     * Get activity trends based on date range
     */
    private function getActivityTrends($user, $startDate = null, $endDate = null)
    {
        // Use provided dates or default to last 7 days
        if (!$startDate) {
            $startDate = now()->subDays(6)->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $daysDiff = $start->diffInDays($end);
        
        $days = collect();
        for ($i = $daysDiff; $i >= 0; $i--) {
            $date = $end->copy()->subDays($i);
            $days->push([
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'registrations' => 0,
                'checkins' => 0
            ]);
        }

        // Get registrations for date range (scope by organizer if needed)
        $registrationsQuery = PwaParticipant::selectRaw('DATE(pwa_participants.created_at) as date, COUNT(DISTINCT pwa_participants.id) as count')
            ->whereBetween('pwa_participants.created_at', [$start, $end]);
        if (!$user->hasRole('Administrator')) {
            $registrationsQuery->whereHas('events', function($q) use ($user) {
                $q->where('events.user_id', $user->id);
            });
        }
        $registrations = $registrationsQuery
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get check-ins for date range (scope by organizer if needed)
        $checkinsQuery = AttendanceRecord::selectRaw('DATE(attendance_records.created_at) as date, COUNT(*) as count')
            ->whereBetween('attendance_records.created_at', [$start, $end]);
        if (!$user->hasRole('Administrator')) {
            $checkinsQuery->whereHas('attendanceSession.attendance.event', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $checkins = $checkinsQuery
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Combine data
        $trends = $days->map(function($day) use ($registrations, $checkins) {
            $day['registrations'] = $registrations->get($day['date'])->count ?? 0;
            $day['checkins'] = $checkins->get($day['date'])->count ?? 0;
            return $day;
        });

        return $trends;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($user)
    {
        $activities = collect();
        
        // Recent PWA participant registrations
        $recentParticipants = PwaParticipant::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($participant) {
                return [
                    'type' => 'registration',
                    'description' => 'New PWA participant registered: ' . $participant->name,
                    'date' => $participant->created_at,
                    'user' => $participant->creator->name ?? 'System'
                ];
            });
            
        $activities = $activities->merge($recentParticipants);
        
        // Recent attendance records
        $recentAttendance = AttendanceRecord::with(['attendanceSession.attendance.event', 'participant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($record) {
                return [
                    'type' => 'attendance',
                    'description' => 'Check-in recorded for ' . ($record->participant->name ?? 'Unknown') . ' at ' . ($record->attendanceSession->attendance->event->name ?? 'Unknown Event'),
                    'date' => $record->created_at,
                    'user' => 'QR Scanner'
                ];
            });
            
        $activities = $activities->merge($recentAttendance);
        
        // Recent certificates
        $recentCertificates = Certificate::with(['event', 'participant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($certificate) {
                return [
                    'type' => 'certificate',
                    'description' => 'Certificate issued for ' . ($certificate->participant->name ?? 'Unknown') . ' at ' . ($certificate->event->name ?? 'Unknown Event'),
                    'date' => $certificate->created_at,
                    'user' => 'System'
                ];
            });
            
        $activities = $activities->merge($recentCertificates);
        
        // Sort by date and take top 10
        return $activities->sortByDesc('date')->take(10);
    }

    /**
     * Get export data
     */
    private function getExportData($eventIds = null)
    {
        $data = [];
        
        // Basic stats
        $data[] = ['Total PWA Participants', PwaParticipant::count(), date('Y-m-d')];
        $data[] = ['Total Events', Event::count(), date('Y-m-d')];
        $data[] = ['Total Attendance Records', AttendanceRecord::count(), date('Y-m-d')];
        $data[] = ['Total Certificates', Certificate::count(), date('Y-m-d')];
        
        // Monthly stats
        $monthlyStats = $this->getMonthlyStats($eventIds);
        foreach ($monthlyStats as $stat) {
            $data[] = ['PWA Registrations - ' . $stat['month'], $stat['count'], date('Y-m-d')];
        }
        
        return $data;
    }
} 