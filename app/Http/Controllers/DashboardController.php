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
        $eventCumulativeGrowth = $this->calculateCumulativeGrowth($monthlyEvents);
        
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
        $genderDistribution = $participantsQuery->clone()
            ->select('gender', DB::raw('count(*) as count'))
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
        $eventStatusDistribution = $eventsQuery->clone()
            ->select('status', DB::raw('count(*) as count'))
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
            ->whereIn('status', ['completed', 'running', 'scheduled'])
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
                DB::raw('CASE WHEN delivered_count > 0 THEN ROUND((opened_count / delivered_count * 100), 1) ELSE 0 END as open_rate'),
                DB::raw('CASE WHEN opened_count > 0 THEN ROUND((clicked_count / opened_count * 100), 1) ELSE 0 END as click_rate')
            )
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Log campaign performance data for debugging
        // Campaign performance data
        
        // Get attendance rate by event
        $attendanceRateByEvent = DB::table('events')
            ->leftJoin('participants', 'events.id', '=', 'participants.event_id')
            ->leftJoin('attendances', 'events.id', '=', 'attendances.event_id')
            ->leftJoin('attendance_records', function($join) {
                $join->on('attendance_records.attendance_id', '=', 'attendances.id')
                     ->on('attendance_records.participant_id', '=', 'participants.id');
            })
            ->when(!$isAdmin, function($q) {
                $q->where('events.user_id', Auth::id());
            })
            ->whereBetween('events.created_at', [$startDate, $endDate])
            ->select(
                'events.id',
                'events.name',
                DB::raw('COUNT(DISTINCT participants.id) as participants_count'),
                DB::raw('COUNT(DISTINCT attendance_records.id) as attendance_records_count')
            )
            ->groupBy('events.id', 'events.name')
            ->orderBy('events.start_date', 'desc')
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
            ->take(10) // Limit to 10 events
            ->values(); // Re-index the array
        
        // Log attendance rate data for debugging
        // Attendance rate data
        
        // Call all new data preparation methods
        $registrationHeatmap = $this->getRegistrationHeatmap($participantsQuery, $startDate, $endDate);
        $registrationTypeBreakdown = $this->getRegistrationTypeBreakdown($participantsQuery);
        $registrationTypeByMonth = $this->getRegistrationTypeByMonth($participantsQuery, $startDate, $endDate);
        $acquisitionFunnel = $this->getAcquisitionFunnel($startDate, $endDate, $isAdmin);
        $certificateRate = $this->getCertificateGenerationRate($participantsQuery, $certificatesQuery);
        $topEvents = $this->getTopEventsByParticipants($eventsQuery);
        $eventPerformanceMatrix = $this->getEventPerformanceMatrix($startDate, $endDate, $isAdmin);
        $ageGroupDistribution = $this->getAgeGroupDistribution($participantsQuery);
        $eventCategoryDistribution = $this->getEventCategoryDistribution($eventsQuery);
        $emailDeliveryStatus = $this->getEmailDeliveryStatus($campaignsQuery);
        $monthlyComparison = $this->getMonthlyComparison($eventsQuery, $startDate, $endDate);
        
        // Calculate trend indicators for summary cards
        $previousPeriodEvents = $this->getPreviousPeriodData($eventsQuery, $startDate, $endDate);
        $previousPeriodParticipants = $this->getPreviousPeriodData($participantsQuery, $startDate, $endDate);
        $previousPeriodCertificates = $this->getPreviousPeriodData($certificatesQuery, $startDate, $endDate);
        $previousPeriodAttendance = $this->getPreviousPeriodData($attendanceQuery, $startDate, $endDate);
        $previousPeriodCampaigns = $this->getPreviousPeriodData($campaignsQuery, $startDate, $endDate);
        
        // Get table data
        $eventPerformanceTable = $this->getEventPerformanceTable($startDate, $endDate, $isAdmin);
        $monthlySummaryTable = $this->getMonthlySummaryTable($startDate, $endDate, $isAdmin);
        $demographicsTable = $this->getParticipantDemographicsTable($participantsQuery);
        
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
            
            // New visualization data
            'registrationHeatmap' => $registrationHeatmap,
            'registrationTypeBreakdown' => $registrationTypeBreakdown,
            'registrationTypeByMonth' => $registrationTypeByMonth,
            'acquisitionFunnel' => $acquisitionFunnel,
            'certificateRate' => $certificateRate,
            'topEvents' => $topEvents,
            'eventPerformanceMatrix' => $eventPerformanceMatrix,
            'ageGroupDistribution' => $ageGroupDistribution,
            'eventCategoryDistribution' => $eventCategoryDistribution,
            'emailDeliveryStatus' => $emailDeliveryStatus,
            'monthlyComparison' => $monthlyComparison,
            
            // Trend data for summary cards
            'previousPeriodEvents' => $previousPeriodEvents,
            'previousPeriodParticipants' => $previousPeriodParticipants,
            'previousPeriodCertificates' => $previousPeriodCertificates,
            'previousPeriodAttendance' => $previousPeriodAttendance,
            'previousPeriodCampaigns' => $previousPeriodCampaigns,
            
            // Table data
            'eventPerformanceTable' => $eventPerformanceTable,
            'monthlySummaryTable' => $monthlySummaryTable,
            'demographicsTable' => $demographicsTable,
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

    /**
     * Apply role-based filtering to a query builder.
     * Note: This is a helper method but role filtering is already applied in index().
     * This method is kept for potential future use with additional queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool  $isAdmin
     * @param  int|null  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyRoleBasedFilter($query, $isAdmin, $userId = null)
    {
        if (!$isAdmin && $userId) {
            // For Event queries
            if (method_exists($query->getModel(), 'user_id')) {
                $query->where('user_id', $userId);
            }
        }
        
        return $query;
    }

    /**
     * Get registration heatmap data (day of week × hour of day).
     * MUST respect role-based filtering from $participantsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getRegistrationHeatmap($participantsQuery, $startDate, $endDate)
    {
        // Get registration data grouped by day of week and hour
        $registrations = $participantsQuery->clone()
            ->select(
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('HOUR(created_at) as hour_of_day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        // Initialize heatmap structure with all days and hours
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $heatmap = [];
        
        foreach ($days as $day) {
            $heatmap[$day] = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $heatmap[$day][sprintf('%02d:00', $hour)] = 0;
            }
        }

        // Fill in actual registration counts
        foreach ($registrations as $registration) {
            // DAYOFWEEK returns 1=Sunday, 2=Monday, ..., 7=Saturday
            $dayIndex = $registration->day_of_week - 1;
            $dayName = $days[$dayIndex];
            $hourKey = sprintf('%02d:00', $registration->hour_of_day);
            
            $heatmap[$dayName][$hourKey] = $registration->count;
        }

        return $heatmap;
    }

    /**
     * Get registration type breakdown (Verified vs Simplified).
     * MUST respect role-based filtering from $participantsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @return array
     */
    private function getRegistrationTypeBreakdown($participantsQuery)
    {
        $breakdown = $participantsQuery->clone()
            ->select('registration_type', DB::raw('COUNT(*) as count'))
            ->groupBy('registration_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->registration_type => $item->count];
            })
            ->toArray();

        $verified = $breakdown['verified'] ?? 0;
        $simplified = $breakdown['simplified'] ?? 0;
        $total = $verified + $simplified;

        return [
            'verified' => $verified,
            'simplified' => $simplified,
            'total' => $total,
            'verified_percentage' => $total > 0 ? round(($verified / $total) * 100, 1) : 0,
            'simplified_percentage' => $total > 0 ? round(($simplified / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get registration type by month (stacked data).
     * MUST respect role-based filtering from $participantsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getRegistrationTypeByMonth($participantsQuery, $startDate, $endDate)
    {
        $data = $participantsQuery->clone()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                'registration_type',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month', 'registration_type')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Initialize structure
        $months = [];
        $verified = [];
        $simplified = [];

        // Fill in missing months with zero counts
        $period = Carbon::parse($startDate)->startOfMonth()->monthsUntil(Carbon::parse($endDate)->endOfMonth()->addDay());
        
        foreach ($period as $date) {
            $key = $date->format('M Y');
            $months[] = $key;
            $verified[$key] = 0;
            $simplified[$key] = 0;
        }

        // Fill in actual counts
        foreach ($data as $item) {
            $date = Carbon::createFromDate($item->year, $item->month, 1);
            $key = $date->format('M Y');
            
            if ($item->registration_type === 'verified') {
                $verified[$key] = $item->count;
            } elseif ($item->registration_type === 'simplified') {
                $simplified[$key] = $item->count;
            }
        }

        return [
            'months' => $months,
            'verified' => array_values($verified),
            'simplified' => array_values($simplified),
        ];
    }

    /**
     * Get participant acquisition funnel data.
     * MUST apply role-based filtering: if not admin, filter by user_id.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  bool  $isAdmin
     * @return array
     */
    private function getAcquisitionFunnel($startDate, $endDate, $isAdmin)
    {
        $userId = Auth::id();

        // Stage 1: Registered (from participants table)
        $registeredQuery = Participant::whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $registeredQuery->whereHas('event', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        $registered = $registeredQuery->distinct('id')->count('id');

        // Stage 2: Attended (from attendance_records table)
        $attendedQuery = DB::table('attendance_records')
            ->join('participants', 'attendance_records.participant_id', '=', 'participants.id')
            ->whereBetween('attendance_records.created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $attendedQuery->join('events', 'participants.event_id', '=', 'events.id')
                ->where('events.user_id', $userId);
        }
        $attended = $attendedQuery->distinct('attendance_records.participant_id')->count('attendance_records.participant_id');

        // Stage 3: Completed (events with completion status)
        // Assuming participants whose events have status 'completed'
        $completedQuery = Participant::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('event', function($q) use ($isAdmin, $userId) {
                $q->where('status', 'completed');
                if (!$isAdmin) {
                    $q->where('user_id', $userId);
                }
            });
        $completed = $completedQuery->distinct('id')->count('id');

        // Stage 4: Certified (from certificates table)
        $certifiedQuery = DB::table('certificates')
            ->whereBetween('certificates.created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $certifiedQuery->join('events', 'certificates.event_id', '=', 'events.id')
                ->where('events.user_id', $userId);
        }
        $certified = $certifiedQuery->distinct('certificates.participant_id')->count('certificates.participant_id');

        // Calculate percentages and drop-offs
        $registeredPct = 100;
        $attendedPct = $registered > 0 ? round(($attended / $registered) * 100, 1) : 0;
        $completedPct = $registered > 0 ? round(($completed / $registered) * 100, 1) : 0;
        $certifiedPct = $registered > 0 ? round(($certified / $registered) * 100, 1) : 0;

        $dropoffAttended = $registered > 0 ? round((($registered - $attended) / $registered) * 100, 1) : 0;
        $dropoffCompleted = $attended > 0 ? round((($attended - $completed) / $attended) * 100, 1) : 0;
        $dropoffCertified = $completed > 0 ? round((($completed - $certified) / $completed) * 100, 1) : 0;

        return [
            'stages' => ['Registered', 'Attended', 'Completed', 'Certified'],
            'counts' => [$registered, $attended, $completed, $certified],
            'percentages' => [$registeredPct, $attendedPct, $completedPct, $certifiedPct],
            'dropoff' => [0, $dropoffAttended, $dropoffCompleted, $dropoffCertified],
        ];
    }

    /**
     * Get certificate generation rate.
     * MUST respect role-based filtering from queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @param  \Illuminate\Database\Eloquent\Builder  $certificatesQuery
     * @return float
     */
    private function getCertificateGenerationRate($participantsQuery, $certificatesQuery)
    {
        $totalParticipants = $participantsQuery->clone()->count();
        $totalCertificates = $certificatesQuery->clone()->count();

        if ($totalParticipants > 0) {
            return round(($totalCertificates / $totalParticipants) * 100, 1);
        }

        return 0;
    }

    /**
     * Get top N events by participant count.
     * MUST respect role-based filtering from $eventsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $eventsQuery
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    private function getTopEventsByParticipants($eventsQuery, $limit = 10)
    {
        return $eventsQuery->clone()
            ->withCount('participants')
            ->orderBy('participants_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'participant_count' => $event->participants_count,
                ];
            });
    }

    /**
     * Get event performance matrix data (for bubble chart).
     * MUST apply role-based filtering: if not admin, filter by user_id.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  bool  $isAdmin
     * @return \Illuminate\Support\Collection
     */
    private function getEventPerformanceMatrix($startDate, $endDate, $isAdmin)
    {
        $userId = Auth::id();

        $query = DB::table('events')
            ->leftJoin('participants', 'events.id', '=', 'participants.event_id')
            ->leftJoin('attendances', 'events.id', '=', 'attendances.event_id')
            ->leftJoin('attendance_records', function($join) {
                $join->on('attendance_records.attendance_id', '=', 'attendances.id')
                     ->on('attendance_records.participant_id', '=', 'participants.id');
            })
            ->leftJoin('certificates', function($join) {
                $join->on('certificates.event_id', '=', 'events.id')
                     ->on('certificates.participant_id', '=', 'participants.id');
            })
            ->whereBetween('events.created_at', [$startDate, $endDate]);

        if (!$isAdmin) {
            $query->where('events.user_id', $userId);
        }

        return $query->select(
                'events.id',
                'events.name',
                DB::raw('COUNT(DISTINCT participants.id) as participant_count'),
                DB::raw('COUNT(DISTINCT attendance_records.id) as attendance_count'),
                DB::raw('COUNT(DISTINCT certificates.id) as certificate_count')
            )
            ->groupBy('events.id', 'events.name')
            ->havingRaw('COUNT(DISTINCT participants.id) >= 1')
            ->get()
            ->map(function($event) {
                $attendanceRate = $event->participant_count > 0 
                    ? round(($event->attendance_count / $event->participant_count) * 100, 1)
                    : 0;
                
                return [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'participant_count' => $event->participant_count,
                    'attendance_rate' => $attendanceRate,
                    'certificate_count' => $event->certificate_count,
                ];
            });
    }

    /**
     * Get participant age group distribution.
     * MUST respect role-based filtering from $participantsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @return array
     */
    private function getAgeGroupDistribution($participantsQuery)
    {
        $participants = $participantsQuery->clone()
            ->select('date_of_birth')
            ->whereNotNull('date_of_birth')
            ->get();

        $ageGroups = [
            'Under 18' => 0,
            '18-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55-64' => 0,
            '65 and above' => 0,
            'Not Specified' => 0,
        ];

        // Count participants without date_of_birth
        $notSpecified = $participantsQuery->clone()
            ->whereNull('date_of_birth')
            ->count();
        $ageGroups['Not Specified'] = $notSpecified;

        // Calculate age groups
        foreach ($participants as $participant) {
            $age = Carbon::now()->diffInYears(Carbon::parse($participant->date_of_birth));
            
            if ($age < 18) {
                $ageGroups['Under 18']++;
            } elseif ($age >= 18 && $age <= 24) {
                $ageGroups['18-24']++;
            } elseif ($age >= 25 && $age <= 34) {
                $ageGroups['25-34']++;
            } elseif ($age >= 35 && $age <= 44) {
                $ageGroups['35-44']++;
            } elseif ($age >= 45 && $age <= 54) {
                $ageGroups['45-54']++;
            } elseif ($age >= 55 && $age <= 64) {
                $ageGroups['55-64']++;
            } else {
                $ageGroups['65 and above']++;
            }
        }

        return $ageGroups;
    }

    /**
     * Get event category distribution.
     * MUST respect role-based filtering from $eventsQuery.
     * 
     * NOTE: The 'category' column doesn't exist in the events table yet.
     * This method returns a placeholder until the column is added.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $eventsQuery
     * @return array
     */
    private function getEventCategoryDistribution($eventsQuery)
    {
        // TODO: Add 'category' column to events table
        // For now, return placeholder data
        return [
            'Uncategorized' => $eventsQuery->clone()->count()
        ];
        
        /* Original implementation (requires 'category' column):
        $categories = $eventsQuery->clone()
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                $categoryLabel = $item->category ?: 'Uncategorized';
                return [$categoryLabel => $item->count];
            })
            ->toArray();

        return $categories;
        */
    }

    /**
     * Get email delivery status breakdown.
     * MUST respect role-based filtering from $campaignsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $campaignsQuery
     * @return array
     */
    private function getEmailDeliveryStatus($campaignsQuery)
    {
        $emailCampaigns = $campaignsQuery->clone()
            ->where('campaign_type', 'email')
            ->select(
                'id',
                'name',
                'recipients_count',
                'delivered_count',
                DB::raw('(recipients_count - delivered_count) as failed_count'),
                DB::raw('0 as bounced_count')
            )
            ->get();

        $result = [];
        foreach ($emailCampaigns as $campaign) {
            $result[] = [
                'campaign_name' => $campaign->name,
                'success' => $campaign->delivered_count,
                'failed' => $campaign->failed_count,
                'bounced' => $campaign->bounced_count,
            ];
        }

        return $result;
    }

    /**
     * Get monthly comparison data (this year vs last year).
     * MUST respect role-based filtering from $eventsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $eventsQuery
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getMonthlyComparison($eventsQuery, $startDate, $endDate)
    {
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        // Get current year data
        $currentYearData = $eventsQuery->clone()
            ->whereYear('start_date', $currentYear)
            ->select(
                DB::raw('MONTH(start_date) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => $item->count];
            })
            ->toArray();

        // Get previous year data
        $previousYearData = $eventsQuery->clone()
            ->whereYear('start_date', $previousYear)
            ->select(
                DB::raw('MONTH(start_date) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => $item->count];
            })
            ->toArray();

        // Prepare month labels and data arrays
        $months = [];
        $currentYearCounts = [];
        $previousYearCounts = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthLabel = Carbon::createFromDate($currentYear, $month, 1)->format('M');
            $months[] = $monthLabel;
            $currentYearCounts[] = $currentYearData[$month] ?? 0;
            $previousYearCounts[] = $previousYearData[$month] ?? 0;
        }

        return [
            'months' => $months,
            'current_year' => $currentYearCounts,
            'previous_year' => $previousYearCounts,
            'current_year_label' => (string)$currentYear,
            'previous_year_label' => (string)$previousYear,
        ];
    }

    /**
     * Get previous period data for trend calculation.
     * MUST respect role-based filtering from $query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return int
     */
    private function getPreviousPeriodData($query, $startDate, $endDate)
    {
        $diffInDays = $startDate->diffInDays($endDate);
        
        $previousStartDate = $startDate->copy()->subDays($diffInDays + 1);
        $previousEndDate = $startDate->copy()->subDay();

        return $query->clone()
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();
    }

    /**
     * Get event performance table data.
     * MUST apply role-based filtering: if not admin, filter by user_id.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  bool  $isAdmin
     * @return \Illuminate\Support\Collection
     */
    private function getEventPerformanceTable($startDate, $endDate, $isAdmin)
    {
        $userId = Auth::id();

        $query = DB::table('events')
            ->leftJoin('participants', 'events.id', '=', 'participants.event_id')
            ->leftJoin('attendances', 'events.id', '=', 'attendances.event_id')
            ->leftJoin('attendance_records', function($join) {
                $join->on('attendance_records.attendance_id', '=', 'attendances.id')
                     ->on('attendance_records.participant_id', '=', 'participants.id');
            })
            ->leftJoin('certificates', function($join) {
                $join->on('certificates.event_id', '=', 'events.id')
                     ->on('certificates.participant_id', '=', 'participants.id');
            })
            ->whereBetween('events.created_at', [$startDate, $endDate]);

        if (!$isAdmin) {
            $query->where('events.user_id', $userId);
        }

        return $query->select(
                'events.id',
                'events.name',
                'events.status',
                DB::raw('COUNT(DISTINCT participants.id) as participants'),
                DB::raw('COUNT(DISTINCT certificates.id) as certificates'),
                DB::raw('COUNT(DISTINCT attendance_records.id) as attendance_count'),
                DB::raw('SUM(CASE WHEN participants.registration_type = "verified" THEN 1 ELSE 0 END) as verified_count'),
                DB::raw('SUM(CASE WHEN participants.registration_type = "simplified" THEN 1 ELSE 0 END) as simplified_count')
            )
            ->groupBy('events.id', 'events.name', 'events.status')
            ->orderBy('events.start_date', 'desc')
            ->get()
            ->map(function($event) {
                $attendanceRate = $event->participants > 0 
                    ? round(($event->attendance_count / $event->participants) * 100, 1)
                    : 0;
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'participants' => $event->participants,
                    'certificates' => $event->certificates,
                    'attendance_rate' => $attendanceRate,
                    'verified_count' => $event->verified_count,
                    'simplified_count' => $event->simplified_count,
                    'status' => $event->status,
                ];
            });
    }

    /**
     * Get monthly summary table data.
     * MUST apply role-based filtering: if not admin, filter by user_id.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  bool  $isAdmin
     * @return array
     */
    private function getMonthlySummaryTable($startDate, $endDate, $isAdmin)
    {
        $userId = Auth::id();

        // Get monthly data for events
        $eventsQuery = Event::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $eventsQuery->where('user_id', $userId);
        }

        $monthlyEvents = $eventsQuery
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => $item->count];
            })
            ->toArray();

        // Get monthly data for participants
        $participantsQuery = Participant::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $participantsQuery->whereHas('event', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $monthlyParticipants = $participantsQuery
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN registration_type = "verified" THEN 1 ELSE 0 END) as verified'),
                DB::raw('SUM(CASE WHEN registration_type = "simplified" THEN 1 ELSE 0 END) as simplified')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => [
                    'count' => $item->count,
                    'verified' => $item->verified,
                    'simplified' => $item->simplified,
                ]];
            })
            ->toArray();

        // Get monthly data for certificates
        $certificatesQuery = Certificate::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $certificatesQuery->whereHas('event', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $monthlyCertificates = $certificatesQuery
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => $item->count];
            })
            ->toArray();

        // Get monthly attendance data
        $attendanceQuery = DB::table('attendance_records')
            ->join('participants', 'attendance_records.participant_id', '=', 'participants.id')
            ->whereBetween('attendance_records.created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $attendanceQuery->join('events', 'participants.event_id', '=', 'events.id')
                ->where('events.user_id', $userId);
        }

        $monthlyAttendance = $attendanceQuery
            ->select(
                DB::raw('YEAR(attendance_records.created_at) as year'),
                DB::raw('MONTH(attendance_records.created_at) as month'),
                DB::raw('COUNT(DISTINCT attendance_records.id) as count')
            )
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [$date->format('M Y') => $item->count];
            })
            ->toArray();

        // Combine all data
        $result = [];
        $period = Carbon::parse($startDate)->startOfMonth()->monthsUntil(Carbon::parse($endDate)->endOfMonth()->addDay());
        
        foreach ($period as $date) {
            $key = $date->format('M Y');
            $events = $monthlyEvents[$key] ?? 0;
            $participants = $monthlyParticipants[$key]['count'] ?? 0;
            $verified = $monthlyParticipants[$key]['verified'] ?? 0;
            $simplified = $monthlyParticipants[$key]['simplified'] ?? 0;
            $certificates = $monthlyCertificates[$key] ?? 0;
            $attendance = $monthlyAttendance[$key] ?? 0;
            
            $attendanceRate = $participants > 0 
                ? round(($attendance / $participants) * 100, 1)
                : 0;

            $result[$key] = [
                'events' => $events,
                'participants' => $participants,
                'certificates' => $certificates,
                'attendance_rate' => $attendanceRate,
                'verified' => $verified,
                'simplified' => $simplified,
            ];
        }

        return $result;
    }

    /**
     * Get participant demographics table data.
     * MUST respect role-based filtering from $participantsQuery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $participantsQuery
     * @return array
     */
    private function getParticipantDemographicsTable($participantsQuery)
    {
        $participants = $participantsQuery->clone()
            ->select('date_of_birth', 'gender')
            ->get();

        $demographics = [
            'Under 18' => ['male' => 0, 'female' => 0, 'total' => 0],
            '18-24' => ['male' => 0, 'female' => 0, 'total' => 0],
            '25-34' => ['male' => 0, 'female' => 0, 'total' => 0],
            '35-44' => ['male' => 0, 'female' => 0, 'total' => 0],
            '45-54' => ['male' => 0, 'female' => 0, 'total' => 0],
            '55-64' => ['male' => 0, 'female' => 0, 'total' => 0],
            '65 and above' => ['male' => 0, 'female' => 0, 'total' => 0],
            'Not Specified' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        $totalParticipants = $participants->count();

        foreach ($participants as $participant) {
            $ageGroup = 'Not Specified';
            
            if ($participant->date_of_birth) {
                $age = Carbon::now()->diffInYears(Carbon::parse($participant->date_of_birth));
                
                if ($age < 18) {
                    $ageGroup = 'Under 18';
                } elseif ($age >= 18 && $age <= 24) {
                    $ageGroup = '18-24';
                } elseif ($age >= 25 && $age <= 34) {
                    $ageGroup = '25-34';
                } elseif ($age >= 35 && $age <= 44) {
                    $ageGroup = '35-44';
                } elseif ($age >= 45 && $age <= 54) {
                    $ageGroup = '45-54';
                } elseif ($age >= 55 && $age <= 64) {
                    $ageGroup = '55-64';
                } else {
                    $ageGroup = '65 and above';
                }
            }

            $demographics[$ageGroup]['total']++;
            
            if ($participant->gender === 'male') {
                $demographics[$ageGroup]['male']++;
            } elseif ($participant->gender === 'female') {
                $demographics[$ageGroup]['female']++;
            }
        }

        // Calculate percentages and format result
        $result = [];
        foreach ($demographics as $ageGroup => $data) {
            $percentage = $totalParticipants > 0 
                ? round(($data['total'] / $totalParticipants) * 100, 1)
                : 0;

            $result[] = [
                'age_group' => $ageGroup,
                'male' => $data['male'],
                'female' => $data['female'],
                'total' => $data['total'],
                'percentage' => $percentage,
            ];
        }

        return $result;
    }
}
