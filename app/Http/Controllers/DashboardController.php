<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Certificate;
use App\Models\Campaign;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with analytics data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get user role
        $isAdmin = Auth::user()->hasRole('Administrator');
        
        // Get filter parameters
        $period = $request->input('period', 'this_month'); // this_month, last_month, last_3_months, last_6_months, this_year, custom
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Set date range based on period
        switch ($period) {
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfDay();
                $endDate = Carbon::now();
                break;
            case 'last_6_months':
                $startDate = Carbon::now()->subMonths(6)->startOfDay();
                $endDate = Carbon::now();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now();
                break;
            case 'custom':
                $startDate = Carbon::parse($startDate)->startOfDay();
                $endDate = Carbon::parse($endDate)->endOfDay();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
        }
        
        // Get base queries with role and date filters
        $eventsQuery = Event::query()
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                      ->orWhereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            });
            
        $participantsQuery = Participant::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        $certificatesQuery = Certificate::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        $campaignsQuery = Campaign::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        $attendanceQuery = AttendanceRecord::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply role-based filters
        if (!$isAdmin) {
            $userId = Auth::id();
            
            $eventsQuery->where('user_id', $userId);
            
            $participantsQuery->whereHas('event', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
            
            $certificatesQuery->whereHas('event', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
            
            $campaignsQuery->where('user_id', $userId);
            
            $attendanceQuery->whereHas('attendance', function($q) use ($userId) {
                $q->whereHas('event', function($q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
            });
        }
        
        // Get summary statistics
        $totalEvents = $eventsQuery->count();
        $totalParticipants = $participantsQuery->count();
        $totalCertificates = $certificatesQuery->count();
        $totalAttendance = $attendanceQuery->count();
        $activeCampaigns = $campaignsQuery->count(); // Count all campaigns, not just running/scheduled
        
        // Get monthly event counts for chart
        $monthlyEvents = $this->getMonthlyEventsData($eventsQuery, $startDate, $endDate);
        
        // Ensure we have at least some data for events over time
        if (empty($monthlyEvents)) {
            // Add current month with zero count if no data
            $monthlyEvents = [Carbon::now()->format('M Y') => 0];
        }
        
        // Calculate trend analysis for events
        $eventTrend = $this->calculateTrend(array_values($monthlyEvents));
        
        // Calculate cumulative growth for events
        $eventCumulativeGrowth = $this->calculateCumulativeGrowth(array_values($monthlyEvents));
        
        // Calculate comparative analysis for events (current vs previous period)
        $eventComparison = $this->calculatePeriodComparison($monthlyEvents);
        
        // Log events data for debugging
        // Dashboard data prepared
        
        // Get monthly participant counts for chart
        $monthlyParticipants = $this->getMonthlyData($participantsQuery, $startDate, $endDate);
        
        // Ensure we have at least some data for participants over time
        if (empty($monthlyParticipants)) {
            // Add current month with zero count if no data
            $monthlyParticipants = [Carbon::now()->format('M Y') => 0];
        }
        
        // Log participants data for debugging
        // Monthly participants data
        
        // Get monthly certificate counts for chart
        $monthlyCertificates = $this->getMonthlyData($certificatesQuery, $startDate, $endDate);
        
        // Get monthly attendance counts for chart
        $monthlyAttendance = $this->getMonthlyData($attendanceQuery, $startDate, $endDate);
        
        // Get participant gender distribution
        $genderDistribution = $participantsQuery->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(function ($item) {
                $genderLabel = $item->gender ? $item->gender : 'Not Specified';
                return [$genderLabel => $item->count];
            })
            ->toArray();
        
        // Ensure we have at least some data for gender distribution
        if (empty($genderDistribution)) {
            $genderDistribution = ['No Data' => 1];
        }
        
        // Log gender distribution for debugging
        // Gender distribution data
        
        // Get event status distribution
        $eventStatusDistribution = $eventsQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                $statusLabel = $item->status ? ucfirst($item->status) : 'Not Specified';
                return [$statusLabel => $item->count];
            })
            ->toArray();
        
        // Ensure we have at least some data for event status distribution
        if (empty($eventStatusDistribution)) {
            $eventStatusDistribution = ['No Data' => 1];
        }
        
        // Log event status distribution for debugging
        // Event status distribution data
        
        // Get campaign performance data (open rates, click rates)
        $campaignPerformance = DB::table('campaigns')
            ->where('campaign_type', 'email')
            ->where('status', 'completed')
            ->when(!$isAdmin, function($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'id',
                'name',
                'delivered_count',
                'opened_count',
                'clicked_count',
                DB::raw('CASE WHEN delivered_count > 0 THEN (opened_count / delivered_count * 100) ELSE 0 END as open_rate'),
                DB::raw('CASE WHEN opened_count > 0 THEN (clicked_count / opened_count * 100) ELSE 0 END as click_rate')
            )
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Ensure we have at least some data for campaign performance
        if ($campaignPerformance->isEmpty()) {
            // Create a dummy campaign performance data
            $campaignPerformance = collect([
                [
                    'id' => 0,
                    'name' => 'No Campaign Data',
                    'delivered_count' => 0,
                    'opened_count' => 0,
                    'clicked_count' => 0,
                    'open_rate' => 0,
                    'click_rate' => 0
                ]
            ]);
        }
        
        // Log campaign performance data for debugging
        // Campaign performance data
        
        // Get attendance rate by event
        $attendanceRateByEvent = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->leftJoin('attendance_records', 'attendance_records.attendance_id', '=', 'attendances.id')
            ->select(
                'events.id',
                'events.name',
                DB::raw('COUNT(DISTINCT attendance_records.participant_id) as attendance_records_count'),
                DB::raw('(SELECT COUNT(*) FROM participants WHERE participants.event_id = events.id) as participants_count')
            )
            ->groupBy('events.id', 'events.name')
            ->orderBy('events.start_date', 'desc')
            ->when(!$isAdmin, function($q) {
                $q->where('events.user_id', Auth::id());
            })
            ->get()
            ->map(function($event) {
                // Calculate attendance rate
                $attendanceRate = $event->participants_count > 0 
                    ? round(($event->attendance_records_count / $event->participants_count) * 100, 1)
                    : 0;
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'participants_count' => $event->participants_count,
                    'attendance_records_count' => $event->attendance_records_count,
                    'attendance_rate' => $attendanceRate
                ];
            })
            ->filter(function($event) {
                // Filter out events with no participants
                return $event['participants_count'] > 0;
            })
            ->values(); // Re-index the array
        
        // Ensure we have at least some data for attendance rate
        if ($attendanceRateByEvent->isEmpty()) {
            // Create a dummy attendance rate data
            $attendanceRateByEvent = collect([
                [
                    'id' => 0,
                    'name' => 'No Attendance Data',
                    'participants_count' => 0,
                    'attendance_records_count' => 0,
                    'attendance_rate' => 0
                ]
            ]);
        }
        
        // Log attendance rate data for debugging
        // Attendance rate data
        
        return view('dashboard', [
            'isAdmin' => $isAdmin,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalEvents' => $totalEvents,
            'totalParticipants' => $totalParticipants,
            'totalCertificates' => $totalCertificates,
            'totalAttendance' => $totalAttendance,
            'activeCampaigns' => $activeCampaigns,
            'monthlyEvents' => $monthlyEvents,
            'monthlyParticipants' => $monthlyParticipants,
            'monthlyCertificates' => $monthlyCertificates,
            'monthlyAttendance' => $monthlyAttendance,
            'genderDistribution' => $genderDistribution,
            'eventStatusDistribution' => $eventStatusDistribution,
            'campaignPerformance' => $campaignPerformance,
            'attendanceRateByEvent' => $attendanceRateByEvent,
            // Analytics data
            'eventTrend' => $eventTrend,
            'eventCumulativeGrowth' => $eventCumulativeGrowth,
            'eventComparison' => $eventComparison,
        ]);
    }
    
    /**
     * Get monthly data for charts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getMonthlyData($query, $startDate, $endDate)
    {
        $diffInMonths = $startDate->diffInMonths($endDate) + 1;
        $result = [];
        
        // If period is less than a month, get daily data
        if ($diffInMonths <= 1) {
            $result = $query->clone()
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [Carbon::parse($item->date)->format('d M') => $item->count];
                })
                ->toArray();
                
            // Fill in missing days with zero counts
            $period = Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)->addDay());
            $filledData = [];
            
            foreach ($period as $date) {
                $key = $date->format('d M');
                $filledData[$key] = $result[$key] ?? 0;
            }
            
            return $filledData;
        }
        
        // For longer periods, get monthly data
        $result = $query->clone()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => $item->count];
            })
            ->toArray();
            
        // Fill in missing months with zero counts
        $period = Carbon::parse($startDate)->startOfMonth()->monthsUntil(Carbon::parse($endDate)->endOfMonth()->addDay());
        $filledData = [];
        
        foreach ($period as $date) {
            $key = $date->format('M Y');
            $filledData[$key] = $result[$key] ?? 0;
        }
        
        return $filledData;
    }

    /**
     * Get monthly event counts for charts based on start_date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getMonthlyEventsData($query, $startDate, $endDate)
    {
        $diffInMonths = $startDate->diffInMonths($endDate) + 1;
        $result = [];

        // If period is less than a month, get daily data
        if ($diffInMonths <= 1) {
            $result = $query->clone()
                ->select(
                    DB::raw('DATE(start_date) as date'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [Carbon::parse($item->date)->format('d M') => $item->count];
                })
                ->toArray();

            // Fill in missing days with zero counts
            $period = Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)->addDay());
            $filledData = [];

            foreach ($period as $date) {
                $key = $date->format('d M');
                $filledData[$key] = $result[$key] ?? 0;
            }

            return $filledData;
        }

        // For longer periods, get monthly data
        $result = $query->clone()
            ->select(
                DB::raw('YEAR(start_date) as year'),
                DB::raw('MONTH(start_date) as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => $item->count];
            })
            ->toArray();

        // Fill in missing months with zero counts
        $period = Carbon::parse($startDate)->startOfMonth()->monthsUntil(Carbon::parse($endDate)->endOfMonth()->addDay());
        $filledData = [];

        foreach ($period as $date) {
            $key = $date->format('M Y');
            $filledData[$key] = $result[$key] ?? 0;
        }

        return $filledData;
    }

    /**
     * Calculate trend analysis for a given monthly data array.
     *
     * @param  array  $monthlyData
     * @return array
     */
    private function calculateTrend($monthlyData)
    {
        $trend = [];
        $currentMonthCount = $monthlyData[0] ?? 0;
        $previousMonthCount = $monthlyData[1] ?? 0;

        if ($currentMonthCount > 0 && $previousMonthCount > 0) {
            $growthRate = ($currentMonthCount - $previousMonthCount) / $previousMonthCount * 100;
            $trend = [
                'growth_rate' => round($growthRate, 2),
                'trend' => $growthRate > 0 ? 'Increasing' : ($growthRate < 0 ? 'Decreasing' : 'Stable')
            ];
        } else {
            $trend = [
                'growth_rate' => 0,
                'trend' => 'No Data'
            ];
        }

        return $trend;
    }

    /**
     * Calculate cumulative growth for a given monthly data array.
     *
     * @param  array  $monthlyData
     * @return array
     */
    private function calculateCumulativeGrowth($monthlyData)
    {
        $cumulativeGrowth = [];
        $totalCount = 0;
        $currentMonthCount = $monthlyData[0] ?? 0;

        foreach ($monthlyData as $month => $count) {
            $totalCount += $count;
            $cumulativeGrowth[$month] = $totalCount;
        }

        return $cumulativeGrowth;
    }

    /**
     * Calculate comparative analysis for a given monthly data array.
     *
     * @param  array  $monthlyData
     * @return array
     */
    private function calculatePeriodComparison($monthlyData)
    {
        $currentPeriodCount = $monthlyData[0] ?? 0;
        $previousPeriodCount = $monthlyData[1] ?? 0;

        if ($currentPeriodCount > 0 && $previousPeriodCount > 0) {
            $growthRate = ($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount * 100;
            $comparison = [
                'current_period_count' => $currentPeriodCount,
                'previous_period_count' => $previousPeriodCount,
                'growth_rate' => round($growthRate, 2),
                'comparison' => $growthRate > 0 ? 'Increased' : ($growthRate < 0 ? 'Decreased' : 'Stable')
            ];
        } else {
            $comparison = [
                'current_period_count' => $currentPeriodCount,
                'previous_period_count' => $previousPeriodCount,
                'growth_rate' => 0,
                'comparison' => 'No Data'
            ];
        }

        return $comparison;
    }
} 