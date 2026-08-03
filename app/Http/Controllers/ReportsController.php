<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Event ids this account may report on.
     */
    private function scopedEventIds(): array
    {
        return Event::when(! auth()->user()->hasRole('Administrator'),
            fn ($q) => $q->where('user_id', auth()->id())
        )->pluck('id')->all();
    }

    /**
     * Sessions matching the current filters, before paging.
     *
     * Shared by the page and the export so the CSV cannot disagree with the screen.
     */
    private function sessionQuery(Request $request, array $eventIds)
    {
        $query = AttendanceSession::query()
            ->whereHas('attendance', fn ($q) => $q->whereIn('event_id', $eventIds));

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('attendance.event', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('event_filter')) {
            $query->whereHas('attendance', fn ($q) => $q->where('event_id', $request->event_filter));
        }

        // Backwards-looking ranges. These used to count forwards from today, so
        // "This Week" asked for the next seven days and matched nothing.
        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today' => $query->whereDate('date', now()->toDateString()),
                'week' => $query->whereDate('date', '>=', now()->subDays(7)->toDateString()),
                'month' => $query->whereDate('date', '>=', now()->startOfMonth()->toDateString()),
                'upcoming' => $query->whereDate('date', '>', now()->toDateString()),
                'past' => $query->whereDate('date', '<', now()->toDateString()),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Turn sessions into report rows.
     *
     * The counts come from two grouped queries rather than two queries per row. The
     * previous version issued a participant count and a record count inside the row
     * loop, so a page of ten sessions cost twenty extra round trips.
     */
    private function sessionRows($sessions)
    {
        $sessionIds = $sessions->pluck('id')->all();
        $eventIds = $sessions->pluck('attendance.event_id')->filter()->unique()->all();

        $registeredByEvent = Participant::whereIn('event_id', $eventIds)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        // 'present' only, matching what the summary counts. The two used to disagree:
        // the summary counted every record regardless of status.
        $attendedBySession = AttendanceRecord::whereIn('attendance_session_id', $sessionIds)
            ->where('status', 'present')
            ->selectRaw('attendance_session_id, COUNT(DISTINCT participant_id) as total')
            ->groupBy('attendance_session_id')
            ->pluck('total', 'attendance_session_id');

        return $sessions->map(function ($session) use ($registeredByEvent, $attendedBySession) {
            $event = $session->attendance->event ?? null;
            $registered = (int) ($registeredByEvent[$session->attendance->event_id ?? 0] ?? 0);
            $attended = (int) ($attendedBySession[$session->id] ?? 0);

            return [
                'id' => $session->id,
                'event_name' => $event->name ?? '-',
                'event_location' => $event->location ?? null,
                'session_date' => $session->date,
                'checkin_from' => $session->checkin_start_time,
                'checkin_to' => $session->checkin_end_time,
                'checkout_from' => $session->checkout_start_time,
                'checkout_to' => $session->checkout_end_time,
                'registered' => $registered,
                'attended' => $attended,
                'rate' => $registered > 0 ? round(($attended / $registered) * 100) : 0,
            ];
        });
    }

    public function attendanceIndex(Request $request)
    {
        $eventIds = $this->scopedEventIds();
        $sessionQuery = $this->sessionQuery($request, $eventIds);

        // Counted before paging, so the summary describes the whole filtered set.
        $totalSessions = (clone $sessionQuery)->count();
        $allSessionIds = (clone $sessionQuery)->pluck('id');

        $sessions = $sessionQuery->with(['attendance:id,event_id', 'attendance.event:id,name,location'])
            ->orderByDesc('date')
            ->paginate(\App\Support\SystemSettings::perPage($request, 10))
            ->withQueryString();

        $rows = $this->sessionRows($sessions->getCollection());

        // The rate filter was read from the request and then thrown away, with a
        // comment saying it would be handled later. It is applied here, to the rows
        // that are on screen.
        if ($request->filled('rate_filter')) {
            $rows = $rows->filter(function ($row) use ($request) {
                return match ($request->rate_filter) {
                    'high' => $row['rate'] >= 75,
                    'medium' => $row['rate'] >= 40 && $row['rate'] < 75,
                    'low' => $row['rate'] < 40,
                    default => true,
                };
            })->values();
        }

        $events = Event::whereIn('id', $eventIds)->orderBy('name')->get(['id', 'name']);

        $totalAttendees = AttendanceRecord::whereIn('attendance_session_id', $allSessionIds)
            ->where('status', 'present')
            ->distinct()
            ->count('participant_id');

        $participantsQuery = Participant::whereIn('event_id', $eventIds);

        if ($request->filled('event_filter')) {
            $participantsQuery->where('event_id', $request->event_filter);
        }

        $totalRegistered = $participantsQuery->count();
        $averageAttendanceRate = $totalRegistered > 0
            ? round(($totalAttendees / $totalRegistered) * 100)
            : 0;

        $tableRows = $rows;

        return view('reports.attendance', compact(
            'events', 
            'tableRows', 
            'totalSessions', 
            'totalAttendees', 
            'averageAttendanceRate',
            'totalRegistered',
            'sessions' // Pass the paginated sessions to the view
        ));
    }

    /**
     * Export the filtered attendance sessions as CSV.
     *
     * This returned {"success":true,"message":"Export not implemented."} as raw JSON
     * in the browser window, from a button that said Export.
     */
    public function attendanceExport(Request $request)
    {
        $eventIds = $this->scopedEventIds();

        $sessions = $this->sessionQuery($request, $eventIds)
            ->with(['attendance:id,event_id', 'attendance.event:id,name,location'])
            ->orderByDesc('date')
            ->get();

        $rows = $this->sessionRows($sessions);

        $filename = 'attendance-sessions-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Event', 'Location', 'Session Date',
                'Check-in From', 'Check-in To', 'Check-out From', 'Check-out To',
                'Registered', 'Attended', 'Attendance Rate %',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['event_name'],
                    $row['event_location'],
                    $row['session_date'],
                    $row['checkin_from'],
                    $row['checkin_to'],
                    $row['checkout_from'],
                    $row['checkout_to'],
                    $row['registered'],
                    $row['attended'],
                    $row['rate'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function attendanceShow($id)
    {
        // Show details for a session
        $session = AttendanceSession::with(['attendance.event'])->findOrFail($id);
        
        // Check if user has permission to view this session
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $session->attendance->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.attendance.index')
                    ->with('error', 'You do not have permission to view this attendance session.');
            }
        }
        
        // Get all registered participants for this event with their attendance records (if any)
        $eventId = $session->attendance->event_id ?? 0;
        $participants = \App\Models\Participant::where('event_id', $eventId)->get();

        // One query for the whole session instead of one per participant. On an event
        // with 345 registrations that loop issued 345 round trips before the page
        // could render.
        $recordsByParticipant = AttendanceRecord::where('attendance_session_id', $id)
            ->get()
            ->keyBy('participant_id');

        // Certificates for the same set, also in one query. The view used to look each
        // one up inside its row loop.
        $certificateByParticipant = \App\Models\Certificate::where('event_id', $eventId)
            ->whereIn('participant_id', $participants->pluck('id'))
            ->get()
            ->keyBy('participant_id');

        $records = $participants->map(function ($participant) use ($recordsByParticipant, $certificateByParticipant) {
            $attendanceRecord = $recordsByParticipant->get($participant->id);

            // Create a unified record object
            return (object) [
                'id' => $attendanceRecord->id ?? null,
                'participant_id' => $participant->id,
                'participant' => $participant,
                'checkin_time' => $attendanceRecord->checkin_time ?? null,
                'checkout_time' => $attendanceRecord->checkout_time ?? null,
                'status' => $attendanceRecord ? $attendanceRecord->status : 'absent',
                'scanned_by_device' => $attendanceRecord->scanned_by_device ?? null,
                'certificate' => $certificateByParticipant->get($participant->id),
            ];
        });
        
        // Calculate real analytics data
        $analytics = [
            'avgDuration' => '0h 0m',
            'attendanceRate' => 0,
            'earlyCheckins' => 0,
            'certificateClaims' => 0
        ];

        // Timeline data for check-ins and check-outs
        $timelineData = [
            'hourly' => [
                'checkins' => [],
                'checkouts' => [],
                'peak_checkin_time' => 'N/A',
                'peak_checkout_time' => 'N/A'
            ],
            'daily' => [
                'checkins' => [],
                'checkouts' => [],
                'peak_day' => 'N/A',
                'total_weekly' => 0
            ]
        ];

        // Demographics data
        $demographics = [
            'gender' => [
                'male' => 0,
                'female' => 0,
                'other' => 0,
                'unknown' => 0,
                'male_percent' => 0,
                'female_percent' => 0,
                'other_percent' => 0,
                'unknown_percent' => 0,
            ],
            'age_groups' => [
                'under_18' => 0,
                '18_24' => 0,
                '25_34' => 0,
                '35_44' => 0,
                '45_54' => 0,
                '55_plus' => 0,
                'unknown' => 0,
                'under_18_percent' => 0,
                '18_24_percent' => 0,
                '25_34_percent' => 0,
                '35_44_percent' => 0,
                '45_54_percent' => 0,
                '55_plus_percent' => 0,
            ],
            'total_attendees' => 0,
            'avg_age' => 0,
            'first_time' => 0,
            'returning' => 0,
            'first_time_percent' => 0,
        ];

        if ($records->count() > 0) {
            // Calculate average duration
            $totalMinutes = 0;
            $recordsWithDuration = 0;
            
            // For timeline data
            $hourlyCheckins = [];
            $hourlyCheckouts = [];
            $dailyCheckins = [];
            $dailyCheckouts = [];
            
            // Initialize all hours from 0-23 with zero counts for better chart display
            for ($h = 0; $h < 24; $h++) {
                $hourlyCheckins[$h] = 0;
                $hourlyCheckouts[$h] = 0;
            }
            
            // Initialize all days with zero counts
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach ($days as $day) {
                $dailyCheckins[$day] = 0;
                $dailyCheckouts[$day] = 0;
            }
            
            foreach ($records as $record) {
                if ($record->checkin_time && $record->checkout_time) {
                    $checkin = \Carbon\Carbon::parse($record->checkin_time);
                    $checkout = \Carbon\Carbon::parse($record->checkout_time);
                    $durationMinutes = $checkout->diffInMinutes($checkin);
                    $totalMinutes += $durationMinutes;
                    $recordsWithDuration++;
                    
                    // Hourly timeline data
                    $checkinHour = (int)$checkin->format('G'); // 24-hour format without leading zeros
                    $checkoutHour = (int)$checkout->format('G');
                    
                    $hourlyCheckins[$checkinHour]++;
                    $hourlyCheckouts[$checkoutHour]++;
                    
                    // Daily timeline data
                    $checkinDay = $checkin->format('D');
                    $checkoutDay = $checkout->format('D');
                    
                    if (!isset($dailyCheckins[$checkinDay])) {
                        $dailyCheckins[$checkinDay] = 0;
                    }
                    $dailyCheckins[$checkinDay]++;
                    
                    if (!isset($dailyCheckouts[$checkoutDay])) {
                        $dailyCheckouts[$checkoutDay] = 0;
                    }
                    $dailyCheckouts[$checkoutDay]++;
                }
                
                // Demographics data - Gender
                if ($record->participant) {
                    $demographics['total_attendees']++;
                    
                    if ($record->participant->gender) {
                        $gender = strtolower($record->participant->gender);
                        if ($gender == 'male' || $gender == 'm') {
                            $demographics['gender']['male']++;
                        } elseif ($gender == 'female' || $gender == 'f') {
                            $demographics['gender']['female']++;
                        } else {
                            $demographics['gender']['other']++;
                        }
                    } else {
                        $demographics['gender']['unknown']++;
                    }
                    
                    // Demographics data - Age groups
                    if ($record->participant->date_of_birth) {
                        $age = \Carbon\Carbon::parse($record->participant->date_of_birth)->age;
                        $demographics['avg_age'] += $age;
                        
                        if ($age < 18) {
                            $demographics['age_groups']['under_18']++;
                        } elseif ($age >= 18 && $age <= 24) {
                            $demographics['age_groups']['18_24']++;
                        } elseif ($age >= 25 && $age <= 34) {
                            $demographics['age_groups']['25_34']++;
                        } elseif ($age >= 35 && $age <= 44) {
                            $demographics['age_groups']['35_44']++;
                        } elseif ($age >= 45 && $age <= 54) {
                            $demographics['age_groups']['45_54']++;
                        } else {
                            $demographics['age_groups']['55_plus']++;
                        }
                    } else {
                        $demographics['age_groups']['unknown']++;
                    }
                }
            }
            
            // Finalize average duration
            if ($recordsWithDuration > 0) {
                $avgMinutes = round($totalMinutes / $recordsWithDuration);
                $hours = floor($avgMinutes / 60);
                $minutes = $avgMinutes % 60;
                $analytics['avgDuration'] = "{$hours}h {$minutes}m";
            }
            
            // Finalize timeline data
            if (!empty($hourlyCheckins)) {
                $timelineData['hourly']['checkins'] = $hourlyCheckins;
                $peakCheckinHour = array_search(max($hourlyCheckins), $hourlyCheckins);
                $formattedPeakHour = sprintf('%02d:00', $peakCheckinHour);
                $timelineData['hourly']['peak_checkin_time'] = $formattedPeakHour;
            }
            
            if (!empty($hourlyCheckouts)) {
                $timelineData['hourly']['checkouts'] = $hourlyCheckouts;
                $peakCheckoutHour = array_search(max($hourlyCheckouts), $hourlyCheckouts);
                $formattedPeakHour = sprintf('%02d:00', $peakCheckoutHour);
                $timelineData['hourly']['peak_checkout_time'] = $formattedPeakHour;
            }
            
            if (!empty($dailyCheckins)) {
                $timelineData['daily']['checkins'] = $dailyCheckins;
                $peakDay = array_search(max($dailyCheckins), $dailyCheckins);
                $timelineData['daily']['peak_day'] = $peakDay;
                $timelineData['daily']['total_weekly'] = array_sum($dailyCheckins);
            }
            
            if (!empty($dailyCheckouts)) {
                $timelineData['daily']['checkouts'] = $dailyCheckouts;
            }
            
            // Finalize demographics data
            if ($demographics['total_attendees'] > 0) {
                $demographics['avg_age'] = round($demographics['avg_age'] / $demographics['total_attendees']);
                
                // Calculate percentages for gender
                foreach (['male', 'female', 'other', 'unknown'] as $gender) {
                    $demographics['gender'][$gender . '_percent'] = round(($demographics['gender'][$gender] / $demographics['total_attendees']) * 100);
                }
                
                // Calculate percentages for age groups
                $totalWithAge = $demographics['total_attendees'] - $demographics['age_groups']['unknown'];
                if ($totalWithAge > 0) {
                    foreach (['under_18', '18_24', '25_34', '35_44', '45_54', '55_plus'] as $ageGroup) {
                        $demographics['age_groups'][$ageGroup . '_percent'] = round(($demographics['age_groups'][$ageGroup] / $totalWithAge) * 100);
                    }
                }
                
                // First-time attendees, counted rather than guessed. This used to be
                // total * 0.3 with the comment "Assuming 30% are first-time", so the
                // card always read 30% no matter who turned up.
                //
                // A person is new if this event is the only one their email appears
                // against. Emails are how the same person is recognised across events
                // everywhere else in the system.
                $attendeeEmails = $records->where('status', 'present')
                    ->pluck('participant.email')
                    ->filter()
                    ->unique();

                $eventId = $session->attendance->event_id ?? 0;

                $returning = $attendeeEmails->isEmpty() ? 0 : Participant::whereIn('email', $attendeeEmails)
                    ->where('event_id', '!=', $eventId)
                    ->distinct()
                    ->pluck('email')
                    ->count();

                $demographics['first_time'] = max(0, $attendeeEmails->count() - $returning);
                $demographics['returning'] = $returning;
                $demographics['first_time_percent'] = $attendeeEmails->count() > 0
                    ? round(($demographics['first_time'] / $attendeeEmails->count()) * 100)
                    : 0;
            }
            
            // Calculate attendance rate
            $registered = Participant::where('event_id', $session->attendance->event_id ?? 0)->count();
            $attended = $records->where('status', 'present')->count();
            $analytics['attendanceRate'] = $registered > 0 ? round(($attended / $registered) * 100) : 0;
            
            // Calculate early check-ins (if checked in before session start time)
            if ($session->checkin_start_time) {
                $sessionStartTime = \Carbon\Carbon::parse($session->date . ' ' . $session->checkin_start_time);
                $earlyCheckins = 0;
                
                foreach ($records as $record) {
                    if ($record->checkin_time && \Carbon\Carbon::parse($record->checkin_time)->lt($sessionStartTime)) {
                        $earlyCheckins++;
                    }
                }
                
                $analytics['earlyCheckins'] = $records->count() > 0 ? 
                    round(($earlyCheckins / $records->count()) * 100) : 0;
            }
            
            // Calculate certificate claims (percentage of participants with certificates)
            $participantIds = $records->pluck('participant_id')->toArray();
            $certificateCount = \App\Models\Certificate::whereIn('participant_id', $participantIds)
                ->where('event_id', $session->attendance->event_id ?? 0)
                ->count();
                
            $analytics['certificateClaims'] = count($participantIds) > 0 ? 
                round(($certificateCount / count($participantIds)) * 100) : 0;
        }
        
        return view('reports.attendance-show', compact('session', 'records', 'analytics', 'timelineData', 'demographics'));
    }

    public function attendanceDelete($id)
    {
        // Delete session and related records
        $session = AttendanceSession::with('attendance.event')->findOrFail($id);
        
        // Check if user has permission to delete this session
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $session->attendance->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.attendance.index')
                    ->with('error', 'You do not have permission to delete this attendance session.');
            }
        }
        
        AttendanceRecord::where('attendance_session_id', $id)->delete();
        $session->delete();
        return redirect()->route('reports.attendance.index')->with('success', 'Attendance session deleted.');
    }
} 