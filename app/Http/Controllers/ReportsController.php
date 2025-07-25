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
    public function attendanceIndex(Request $request)
    {
        // Filters
        $eventId = $request->input('event_filter');
        $dateRange = $request->input('date_range');

        // Get events based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('start_date')->get();
        } else {
            // Organizer only sees their events
            $events = Event::where('user_id', auth()->id())->orderBy('start_date')->get();
        }

        // Query sessions (AttendanceSession) with event
        $sessionsQuery = AttendanceSession::with(['attendance.event']);
        
        // Filter by user role - administrators see all, organizers see only their events
        if (!auth()->user()->hasRole('Administrator')) {
            $sessionsQuery->whereHas('attendance.event', function($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        if ($eventId) {
            $sessionsQuery->whereHas('attendance', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }
        if ($dateRange) {
            // Expecting format: 'YYYY-MM-DD - YYYY-MM-DD'
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $sessionsQuery->whereBetween('date', [$dates[0], $dates[1]]);
            }
        }
        
        // Get total count for summary statistics
        $totalSessionsCount = $sessionsQuery->count();
        $sessionIds = $sessionsQuery->pluck('id');
        
        // Apply pagination
        $perPage = 10; // Number of items per page
        $sessions = $sessionsQuery->orderBy('date', 'desc')->paginate($perPage);

        // Summary
        $totalSessions = $totalSessionsCount;
        $totalAttendees = AttendanceRecord::whereIn('attendance_session_id', $sessionIds)->distinct('participant_id')->count('participant_id');
        
        // Filter participants by user role
        $participantsQuery = Participant::query();
        if (!auth()->user()->hasRole('Administrator')) {
            $userEventIds = Event::where('user_id', auth()->id())->pluck('id');
            $participantsQuery->whereIn('event_id', $userEventIds);
        }
        
        if ($eventId) {
            $participantsQuery->where('event_id', $eventId);
        }
        
        $totalRegistered = $participantsQuery->count();
        $averageAttendanceRate = $totalRegistered > 0 ? round(($totalAttendees / $totalRegistered) * 100) : 0;

        // Table data
        $tableRows = $sessions->map(function($session) {
            $event = $session->attendance->event ?? null;
            $registered = Participant::where('event_id', $event->id ?? 0)->count();
            $attended = AttendanceRecord::where('attendance_session_id', $session->id)->where('status', 'present')->distinct('participant_id')->count('participant_id');
            $rate = $registered > 0 ? round(($attended / $registered) * 100) : 0;
            return [
                'id' => $session->id,
                'event_name' => $event->name ?? '-',
                'session_date' => $session->date,
                'start_time' => $session->checkin_start_time,
                'end_time' => $session->checkin_end_time,
                'registered' => $registered,
                'attended' => $attended,
                'rate' => $rate,
            ];
        });

        return view('reports.attendance', compact(
            'events', 
            'tableRows', 
            'totalSessions', 
            'totalAttendees', 
            'averageAttendanceRate', 
            'eventId', 
            'dateRange',
            'sessions' // Pass the paginated sessions to the view
        ));
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
        
        $records = AttendanceRecord::with('participant')
            ->where('attendance_session_id', $id)
            ->get();
        
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
                'unknown' => 0
            ],
            'age_groups' => [
                'under_18' => 0,
                '18_24' => 0,
                '25_34' => 0,
                '35_44' => 0,
                '45_54' => 0,
                '55_plus' => 0,
                'unknown' => 0
            ],
            'total_attendees' => 0,
            'avg_age' => 0,
            'first_time' => 0
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
                
                // Estimate first-time attendees (placeholder - in real app, would check against historical data)
                $demographics['first_time'] = round($demographics['total_attendees'] * 0.3); // Assuming 30% are first-time
                $demographics['first_time_percent'] = round(($demographics['first_time'] / $demographics['total_attendees']) * 100);
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

    public function attendanceExport(Request $request)
    {
        // Export logic (CSV/Excel) - placeholder
        return response()->json(['success' => true, 'message' => 'Export not implemented.']);
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