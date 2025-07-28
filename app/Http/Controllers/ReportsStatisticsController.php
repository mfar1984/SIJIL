<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Certificate;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsStatisticsController extends Controller
{
    /**
     * Display the statistics dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $dateFilter = $request->input('date_filter', 'last_30');
        $eventType = $request->input('event_type');
        $organizerId = $request->input('organizer');
        
        // Set date range based on filter
        $startDate = null;
        $endDate = Carbon::now();
        
        switch ($dateFilter) {
            case 'last_30':
                $startDate = Carbon::now()->subDays(30);
                break;
            case 'last_90':
                $startDate = Carbon::now()->subDays(90);
                break;
            case 'last_6_months':
                $startDate = Carbon::now()->subMonths(6);
                break;
            case 'last_year':
                $startDate = Carbon::now()->subYear();
                break;
            case 'custom':
                // Custom date range would be handled with additional parameters
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->input('start_date'));
                    $endDate = Carbon::parse($request->input('end_date'));
                } else {
                    $startDate = Carbon::now()->subDays(30); // Default to last 30 days
                }
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
        }
        
        // Build event query with filters
        $eventsQuery = Event::query();
        
        // Filter by user role - administrators see all, organizers see only their events
        if (!auth()->user()->hasRole('Administrator')) {
            $eventsQuery->where('user_id', auth()->id());
            
            // Force organizer filter to current user for non-admin users
            $organizerId = auth()->id();
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $eventsQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        if ($startDate && $endDate) {
            $eventsQuery->whereBetween('start_date', [$startDate, $endDate]);
        }
        
        if ($eventType) {
            // Get all keywords for the selected event type
            $keywords = $this->getKeywordsForEventType($eventType);
            
            // Build a query that checks if the event name contains any of the keywords
            $eventsQuery->where(function($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%{$keyword}%");
                }
            });
        }
        
        if ($organizerId) {
            $eventsQuery->where('user_id', $organizerId);
        }

        // Filter by status
        if ($request->filled('status_filter')) {
            $eventsQuery->where('status', $request->status_filter);
        }
        
        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 5);
        $events = $eventsQuery->orderBy('start_date', 'desc')->paginate($perPage);
        
        // Get previous period for comparison
        $prevPeriodStart = (clone $startDate)->subDays($startDate->diffInDays($endDate));
        $prevPeriodEnd = (clone $startDate)->subDay();
        
        // Get event count statistics
        $totalEvents = $eventsQuery->count();
        $prevPeriodEventsQuery = Event::whereBetween('start_date', [$prevPeriodStart, $prevPeriodEnd]);

        // Filter previous period events by user role
        if (!auth()->user()->hasRole('Administrator')) {
            $prevPeriodEventsQuery->where('user_id', auth()->id());
        }

        if ($eventType) {
            // Get all keywords for the selected event type
            $keywords = $this->getKeywordsForEventType($eventType);
            
            // Build a query that checks if the event name contains any of the keywords
            $prevPeriodEventsQuery->where(function($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%{$keyword}%");
                }
            });
        }

        if ($organizerId) {
            $prevPeriodEventsQuery->where('user_id', $organizerId);
        }

        $prevPeriodEventsCount = $prevPeriodEventsQuery->count();
        
        $eventPercentChange = $prevPeriodEventsCount > 0 
            ? round((($totalEvents - $prevPeriodEventsCount) / $prevPeriodEventsCount) * 100) 
            : 100;
        
        // Get participant count statistics
        $eventIds = $eventsQuery->pluck('id')->toArray();
        $totalParticipants = Participant::whereIn('event_id', $eventIds)->count();
        
        $prevPeriodEventIdsQuery = Event::whereBetween('start_date', [$prevPeriodStart, $prevPeriodEnd]);

        // Filter previous period events by user role
        if (!auth()->user()->hasRole('Administrator')) {
            $prevPeriodEventIdsQuery->where('user_id', auth()->id());
        }

        if ($eventType) {
            // Get all keywords for the selected event type
            $keywords = $this->getKeywordsForEventType($eventType);
            
            // Build a query that checks if the event name contains any of the keywords
            $prevPeriodEventIdsQuery->where(function($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%{$keyword}%");
                }
            });
        }

        if ($organizerId) {
            $prevPeriodEventIdsQuery->where('user_id', $organizerId);
        }

        $prevPeriodEventIds = $prevPeriodEventIdsQuery->pluck('id')->toArray();
        
        $prevPeriodParticipantsCount = Participant::whereIn('event_id', $prevPeriodEventIds)->count();
        
        $participantPercentChange = $prevPeriodParticipantsCount > 0 
            ? round((($totalParticipants - $prevPeriodParticipantsCount) / $prevPeriodParticipantsCount) * 100) 
            : 100;
        
        // Get certificate count statistics
        $totalCertificates = Certificate::whereIn('event_id', $eventIds)->count();
        
        $prevPeriodCertificatesCount = Certificate::whereIn('event_id', $prevPeriodEventIds)->count();
        
        $certificatePercentChange = $prevPeriodCertificatesCount > 0 
            ? round((($totalCertificates - $prevPeriodCertificatesCount) / $prevPeriodCertificatesCount) * 100) 
            : 100;
        
        // Calculate average attendance rate
        $attendanceRates = [];
        $totalAttendanceRate = 0;
        $eventCount = 0;
        
        foreach ($eventIds as $eventId) {
            $attendances = Attendance::where('event_id', $eventId)->get();
            
            foreach ($attendances as $attendance) {
                $sessions = AttendanceSession::where('attendance_id', $attendance->id)->get();
                
                foreach ($sessions as $session) {
                    $registered = Participant::where('event_id', $eventId)->count();
                    $attended = AttendanceRecord::where('attendance_session_id', $session->id)
                        ->where('status', 'present')
                        ->distinct('participant_id')
                        ->count('participant_id');
                    
                    if ($registered > 0) {
                        $rate = round(($attended / $registered) * 100);
                        $attendanceRates[] = $rate;
                        $totalAttendanceRate += $rate;
                        $eventCount++;
                    }
                }
            }
        }
        
        $avgAttendanceRate = $eventCount > 0 ? round($totalAttendanceRate / $eventCount) : 0;
        
        // Calculate previous period attendance rate
        $prevAttendanceRates = [];
        $prevTotalAttendanceRate = 0;
        $prevEventCount = 0;
        
        foreach ($prevPeriodEventIds as $eventId) {
            $attendances = Attendance::where('event_id', $eventId)->get();
            
            foreach ($attendances as $attendance) {
                $sessions = AttendanceSession::where('attendance_id', $attendance->id)->get();
                
                foreach ($sessions as $session) {
                    $registered = Participant::where('event_id', $eventId)->count();
                    $attended = AttendanceRecord::where('attendance_session_id', $session->id)
                        ->where('status', 'present')
                        ->distinct('participant_id')
                        ->count('participant_id');
                    
                    if ($registered > 0) {
                        $rate = round(($attended / $registered) * 100);
                        $prevAttendanceRates[] = $rate;
                        $prevTotalAttendanceRate += $rate;
                        $prevEventCount++;
                    }
                }
            }
        }
        
        $prevAvgAttendanceRate = $prevEventCount > 0 ? round($prevTotalAttendanceRate / $prevEventCount) : 0;
        
        $attendanceRatePercentChange = $prevAvgAttendanceRate > 0 
            ? round((($avgAttendanceRate - $prevAvgAttendanceRate) / $prevAvgAttendanceRate) * 100) 
            : 0;
        
        // Get events by month for chart
        $monthlyEvents = [];
        $currentYear = Carbon::now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::createFromDate($currentYear, $i, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($currentYear, $i, 1)->endOfMonth();
            
            $monthlyQuery = Event::whereBetween('start_date', [$monthStart, $monthEnd]);
            
            // Filter monthly events by user role
            if (!auth()->user()->hasRole('Administrator')) {
                $monthlyQuery->where('user_id', auth()->id());
            }
            
            if ($eventType) {
                // Get all keywords for the selected event type
                $keywords = $this->getKeywordsForEventType($eventType);
                
                // Build a query that checks if the event name contains any of the keywords
                $monthlyQuery->where(function($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            }
            
            if ($organizerId) {
                $monthlyQuery->where('user_id', $organizerId);
            }
            
            $count = $monthlyQuery->count();
            
            $monthlyEvents[$i] = [
                'month' => $monthStart->format('M'),
                'count' => $count
            ];
        }

        // Instead of using event types from database, we'll categorize events by their name
        // since there's no 'type' column in the events table
        $eventTypes = ['Conference', 'Workshop', 'Training', 'Seminar', 'Gaming'];

        // Get attendance rate by event category
        $attendanceByType = [];

        foreach ($eventTypes as $type) {
            // Get all keywords for this event type
            $keywords = $this->getKeywordsForEventType($type);
            
            // Find events whose names contain any of the keywords
            $typeEventsQuery = Event::query();
            
            // Filter by user role
            if (!auth()->user()->hasRole('Administrator')) {
                $typeEventsQuery->where('user_id', auth()->id());
            }
            
            if (!empty($keywords)) {
                $typeEventsQuery->where(function($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            }
            
            if ($startDate && $endDate) {
                $typeEventsQuery->whereBetween('start_date', [$startDate, $endDate]);
            }
            
            if ($organizerId) {
                $typeEventsQuery->where('user_id', $organizerId);
            }
            
            $typeEventIds = $typeEventsQuery->pluck('id')->toArray();
            
            $typeAttendanceRates = [];
            $typeTotalAttendanceRate = 0;
            $typeEventCount = 0;
            
            foreach ($typeEventIds as $eventId) {
                $attendances = Attendance::where('event_id', $eventId)->get();
                
                foreach ($attendances as $attendance) {
                    $sessions = AttendanceSession::where('attendance_id', $attendance->id)->get();
                    
                    foreach ($sessions as $session) {
                        $registered = Participant::where('event_id', $eventId)->count();
                        $attended = AttendanceRecord::where('attendance_session_id', $session->id)
                            ->where('status', 'present')
                            ->distinct('participant_id')
                            ->count('participant_id');
                        
                        if ($registered > 0) {
                            $rate = round(($attended / $registered) * 100);
                            $typeAttendanceRates[] = $rate;
                            $typeTotalAttendanceRate += $rate;
                            $typeEventCount++;
                        }
                    }
                }
            }
            
            $typeAvgAttendanceRate = $typeEventCount > 0 ? round($typeTotalAttendanceRate / $typeEventCount) : 0;
            
            $attendanceByType[$type] = $typeAvgAttendanceRate;
        }
        
        // Get top performing events
        $topEvents = [];
        
        foreach ($events as $event) {
            $registered = Participant::where('event_id', $event->id)->count();
            $attended = 0;
            $certificateCount = Certificate::where('event_id', $event->id)->count();
            
            $attendances = Attendance::where('event_id', $event->id)->get();
            foreach ($attendances as $attendance) {
                $sessions = AttendanceSession::where('attendance_id', $attendance->id)->get();
                foreach ($sessions as $session) {
                    $sessionAttended = AttendanceRecord::where('attendance_session_id', $session->id)
                        ->where('status', 'present')
                        ->distinct('participant_id')
                        ->count('participant_id');
                    $attended = max($attended, $sessionAttended); // Use highest attendance count
                }
            }
            
            $attendanceRate = $registered > 0 ? round(($attended / $registered) * 100) : 0;
            
            $topEvents[] = [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->start_date ? Carbon::parse($event->start_date)->format('d M Y') : 'N/A',
                'type' => $this->determineEventType($event->name),
                'participants' => $registered,
                'attendance_rate' => $attendanceRate,
                'certificates' => $certificateCount
            ];
        }
        
        // Get organizers for filter dropdown
        // For administrators, show all organizers
        // For organizers, only show themselves
        if (auth()->user()->hasRole('Administrator')) {
            $organizers = \App\Models\User::select(['id', 'name'])
                ->whereIn('id', function($query) {
                    $query->select('user_id')
                        ->from('events')
                        ->distinct();
                })
                ->get();
        } else {
            $organizers = \App\Models\User::select(['id', 'name'])
                ->where('id', auth()->id())
                ->get();
        }

        return view('reports.statistics', compact(
            'events',
            'totalEvents',
            'eventPercentChange',
            'totalParticipants',
            'participantPercentChange',
            'totalCertificates',
            'certificatePercentChange',
            'avgAttendanceRate',
            'attendanceRatePercentChange',
            'monthlyEvents',
            'attendanceByType',
            'topEvents',
            'dateFilter',
            'eventType',
            'organizerId',
            'organizers',
            'eventTypes'
        ));
    }

    /**
     * Determine the event type based on event name.
     * This method handles multiple languages including English, Malay, and Chinese.
     * 
     * @param string $eventName
     * @return string
     */
    private function determineEventType($eventName)
    {
        // Convert to lowercase for case-insensitive matching
        $eventNameLower = strtolower($eventName);
        
        // English keywords
        $conferenceKeywords = ['conference', 'symposium', 'congress', 'summit', 'convention'];
        $workshopKeywords = ['workshop', 'hands-on', 'practical session', 'lab'];
        $trainingKeywords = ['training', 'course', 'class', 'lesson', 'coaching'];
        $seminarKeywords = ['seminar', 'webinar', 'talk', 'lecture', 'presentation'];
        
        // Gaming event keywords (new category)
        $gamingKeywords = [
            // Game titles
            'mobile legends', 'ml', 'pubg', 'mobile pubg', 'pubgm', 'free fire', 'cod', 'call of duty', 
            'valorant', 'dota', 'league of legends', 'lol', 'fortnite', 'apex legends', 'genshin', 
            'esports', 'e-sports', 'gaming', 'game', 'tournament', 'competition', 'championship',
            'cup', 'match', 'battle', 'arena', 'showdown', 'playoff', 'qualifier',
            
            // Competition terms
            'pertandingan', 'kejohanan', 'turnamen', 'kompetisi', 'perlawanan', 'piala', 
            'cabaran', 'liga', 'sukan', 'e-sukan',
            
            // Location-specific gaming events often use these patterns
            'peringkat', 'level', 'wilayah', 'daerah', 'negeri', 'kebangsaan', 'antarabangsa'
        ];
        
        // Malay keywords
        $conferenceKeywords = array_merge($conferenceKeywords, ['persidangan', 'konvensyen', 'kongres']);
        $workshopKeywords = array_merge($workshopKeywords, ['bengkel', 'latihan praktikal']);
        $trainingKeywords = array_merge($trainingKeywords, ['latihan', 'kursus', 'kelas', 'bimbingan']);
        $seminarKeywords = array_merge($seminarKeywords, ['seminar', 'ceramah', 'kuliah', 'syarahan']);
        
        // Chinese keywords
        $conferenceKeywords = array_merge($conferenceKeywords, ['会议', '大会', '研讨会']);
        $workshopKeywords = array_merge($workshopKeywords, ['工作坊', '实践课', '实操']);
        $trainingKeywords = array_merge($trainingKeywords, ['培训', '训练', '课程', '教学']);
        $seminarKeywords = array_merge($seminarKeywords, ['讲座', '讲习班', '演讲']);
        $gamingKeywords = array_merge($gamingKeywords, ['电竞', '游戏', '比赛', '锦标赛', '联赛', '电子竞技']);
        
        // Check for gaming event first (highest priority)
        foreach ($gamingKeywords as $keyword) {
            if (mb_stripos($eventNameLower, $keyword) !== false) {
                return 'Gaming';
            }
        }
        
        // Check for each type of keyword
        foreach ($conferenceKeywords as $keyword) {
            if (mb_stripos($eventNameLower, $keyword) !== false) {
                return 'Conference';
            }
        }
        
        foreach ($workshopKeywords as $keyword) {
            if (mb_stripos($eventNameLower, $keyword) !== false) {
                return 'Workshop';
            }
        }
        
        foreach ($trainingKeywords as $keyword) {
            if (mb_stripos($eventNameLower, $keyword) !== false) {
                return 'Training';
            }
        }
        
        foreach ($seminarKeywords as $keyword) {
            if (mb_stripos($eventNameLower, $keyword) !== false) {
                return 'Seminar';
            }
        }
        
        // If no match is found, return 'Other'
        return 'Other';
    }

    /**
     * Get keywords for a specific event type.
     * This method handles multiple languages for event type filtering.
     * 
     * @param string $eventType
     * @return array
     */
    private function getKeywordsForEventType($eventType)
    {
        $keywords = [];

        // English keywords
        $conferenceKeywords = ['conference', 'symposium', 'congress', 'summit', 'convention'];
        $workshopKeywords = ['workshop', 'hands-on', 'practical session', 'lab'];
        $trainingKeywords = ['training', 'course', 'class', 'lesson', 'coaching'];
        $seminarKeywords = ['seminar', 'webinar', 'talk', 'lecture', 'presentation'];
        
        // Gaming event keywords
        $gamingKeywords = [
            // Game titles
            'mobile legends', 'ml', 'pubg', 'mobile pubg', 'pubgm', 'free fire', 'cod', 'call of duty', 
            'valorant', 'dota', 'league of legends', 'lol', 'fortnite', 'apex legends', 'genshin', 
            'esports', 'e-sports', 'gaming', 'game', 'tournament', 'competition', 'championship',
            'cup', 'match', 'battle', 'arena', 'showdown', 'playoff', 'qualifier',
            
            // Competition terms
            'pertandingan', 'kejohanan', 'turnamen', 'kompetisi', 'perlawanan', 'piala', 
            'cabaran', 'liga', 'sukan', 'e-sukan',
            
            // Location-specific gaming events often use these patterns
            'peringkat', 'level', 'wilayah', 'daerah', 'negeri', 'kebangsaan', 'antarabangsa'
        ];

        // Malay keywords
        $conferenceKeywords = array_merge($conferenceKeywords, ['persidangan', 'konvensyen', 'kongres']);
        $workshopKeywords = array_merge($workshopKeywords, ['bengkel', 'latihan praktikal']);
        $trainingKeywords = array_merge($trainingKeywords, ['latihan', 'kursus', 'kelas', 'bimbingan']);
        $seminarKeywords = array_merge($seminarKeywords, ['seminar', 'ceramah', 'kuliah', 'syarahan']);

        // Chinese keywords
        $conferenceKeywords = array_merge($conferenceKeywords, ['会议', '大会', '研讨会']);
        $workshopKeywords = array_merge($workshopKeywords, ['工作坊', '实践课', '实操']);
        $trainingKeywords = array_merge($trainingKeywords, ['培训', '训练', '课程', '教学']);
        $seminarKeywords = array_merge($seminarKeywords, ['讲座', '讲习班', '演讲']);
        $gamingKeywords = array_merge($gamingKeywords, ['电竞', '游戏', '比赛', '锦标赛', '联赛', '电子竞技']);

        switch ($eventType) {
            case 'Conference':
                $keywords = $conferenceKeywords;
                break;
            case 'Workshop':
                $keywords = $workshopKeywords;
                break;
            case 'Training':
                $keywords = $trainingKeywords;
                break;
            case 'Seminar':
                $keywords = $seminarKeywords;
                break;
            case 'Gaming':
                $keywords = $gamingKeywords;
                break;
            default:
                // If eventType is not one of the known types, return empty array
                $keywords = [];
        }

        return $keywords;
    }
} 