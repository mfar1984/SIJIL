<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with('event');

        if ($user->hasRole('Organizer')) {
            // Get event IDs created by the organizer
            $eventIds = \App\Models\Event::where('user_id', $user->id)->pluck('id');
            
            // Filter attendance sessions by those event IDs
            $query->whereIn('event_id', $eventIds);
        }
        // Administrator sees all attendances

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('event', function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status - exclude archived by default unless specifically requested
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // By default, exclude archived items from main attendance list
            $query->where('status', '!=', 'archived');
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->where('date', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('date', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('date', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('date', '<', $today->format('Y-m-d'));
                    break;
            }
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $attendances = $query->orderBy('date', 'desc')->orderBy('start_time', 'asc')->paginate($perPage);

        // Get events for filter dropdown
        if ($user->hasRole('Administrator')) {
            $events = \App\Models\Event::orderBy('name')->get();
        } else {
            $events = \App\Models\Event::where('user_id', $user->id)->orderBy('name')->get();
        }
        
        return view('attendance.index', compact('attendances', 'events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = \App\Models\Event::where('status', 'active')->orderBy('start_date')->get();
        $eventsArray = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'start_date' => $e->start_date,
                'end_date' => $e->end_date,
                'start_time' => $e->start_time,
                'end_time' => $e->end_time,
                'location' => $e->location,
            ];
        })->values()->toArray();
        return view('attendance.create', compact('events', 'eventsArray'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:active,expired,completed',
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date',
            'sessions.*.checkin_start_time' => 'required',
            'sessions.*.checkin_end_time' => 'required',
            'sessions.*.checkout_start_time' => 'nullable',
            'sessions.*.checkout_end_time' => 'nullable',
        ]);

        // Ambil session pertama untuk field utama attendances
        $firstSession = $request->sessions[0] ?? null;
        if (!$firstSession) {
            return back()->with('error', 'At least one session is required.');
        }

        // Create main attendance record (WAJIB isi date, start_time, end_time)
        $attendance = Attendance::create([
            'event_id' => $request->event_id,
            'status' => $request->status,
            'unique_code' => Str::random(32),
            'created_by' => Auth::id(),
            'date' => $firstSession['date'],
            'start_time' => $firstSession['checkin_start_time'],
            'end_time' => $firstSession['checkin_end_time'],
        ]);

        // Create sessions with unique codes
        foreach ($request->sessions as $session) {
            $hasCheckout = !empty($session['checkout_start_time']);
            
            // Check-in session
            $attendance->sessions()->create([
                'unique_code' => \Illuminate\Support\Str::random(32),
                'session_type' => 'checkin',
                'date' => $session['date'],
                'checkin_start_time' => $session['checkin_start_time'],
                'checkin_end_time' => $session['checkin_end_time'],
                'checkout_start_time' => null,
                'checkout_end_time' => null,
            ]);
            
            // If has checkout, create separate checkout session
            if ($hasCheckout) {
                $attendance->sessions()->create([
                    'unique_code' => \Illuminate\Support\Str::random(32),
                    'session_type' => 'checkout',
                    'date' => $session['date'],
                    'checkin_start_time' => null,
                    'checkin_end_time' => null,
                    'checkout_start_time' => $session['checkout_start_time'],
                    'checkout_end_time' => $session['checkout_end_time'],
                ]);
            }
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance session(s) created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        // Return the show attendance session view
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $events = \App\Models\Event::where('status', 'active')->orderBy('name')->get();
        $sessions = $attendance->sessions()->orderBy('date')->get();
        $eventsArray = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'start_date' => $e->start_date,
                'end_date' => $e->end_date,
                'start_time' => $e->start_time,
                'end_time' => $e->end_time,
                'location' => $e->location,
            ];
        })->values()->toArray();
        return view('attendance.edit', compact('attendance', 'events', 'sessions', 'eventsArray'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:active,expired,completed',
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date',
            'sessions.*.checkin_start_time' => 'required',
            'sessions.*.checkin_end_time' => 'required',
            'sessions.*.checkout_start_time' => 'nullable',
            'sessions.*.checkout_end_time' => 'nullable',
        ]);

        // Update main attendance record
        $attendance->update([
            'event_id' => $request->event_id,
            'status' => $request->status,
        ]);

        // Remove old sessions
        $attendance->sessions()->delete();

        // Create new sessions with unique codes
        foreach ($request->sessions as $session) {
            $hasCheckout = !empty($session['checkout_start_time']);
            
            // Check-in session
            $attendance->sessions()->create([
                'unique_code' => \Illuminate\Support\Str::random(32),
                'session_type' => 'checkin',
                'date' => $session['date'],
                'checkin_start_time' => $session['checkin_start_time'],
                'checkin_end_time' => $session['checkin_end_time'],
                'checkout_start_time' => null,
                'checkout_end_time' => null,
            ]);
            
            // If has checkout, create separate checkout session
            if ($hasCheckout) {
                $attendance->sessions()->create([
                    'unique_code' => \Illuminate\Support\Str::random(32),
                    'session_type' => 'checkout',
                    'date' => $session['date'],
                    'checkin_start_time' => null,
                    'checkin_end_time' => null,
                    'checkout_start_time' => $session['checkout_start_time'],
                    'checkout_end_time' => $session['checkout_end_time'],
                ]);
            }
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance session updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        // Delete the attendance record
        $attendance->delete();
        
        return redirect()->route('attendance.index')->with('success', 'Attendance session deleted successfully.');
    }

    public function qrcode(Attendance $attendance)
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        
        // Priority 1: Find session for today that is currently active (within time window)
        $activeSession = $attendance->sessions()
            ->where('date', $today)
            ->where(function($q) use ($currentTime) {
                // Check-in window active now
                $q->where(function($q2) use ($currentTime) {
                    $q2->where('session_type', 'checkin')
                       ->whereNotNull('checkin_start_time')
                       ->whereRaw("TIME(?) BETWEEN TIME(checkin_start_time) AND TIME(checkin_end_time)", [$currentTime]);
                })
                // Checkout window active now
                ->orWhere(function($q2) use ($currentTime) {
                    $q2->where('session_type', 'checkout')
                       ->whereNotNull('checkout_start_time')
                       ->whereRaw("TIME(?) BETWEEN TIME(checkout_start_time) AND TIME(checkout_end_time)", [$currentTime]);
                });
            })
            ->orderBy('session_type') // checkin before checkout if both active
            ->first();
        
        // Priority 2: If no active session now, find next upcoming session (today future or future date)
        if (!$activeSession) {
            $activeSession = $attendance->sessions()
                ->where(function($q) use ($today, $currentTime) {
                    // Future sessions today
                    $q->where(function($q2) use ($today, $currentTime) {
                        $q2->where('date', $today)
                           ->where(function($q3) use ($currentTime) {
                               $q3->where(function($q4) use ($currentTime) {
                                   $q4->where('session_type', 'checkin')
                                      ->whereNotNull('checkin_start_time')
                                      ->whereRaw("TIME(checkin_start_time) > ?", [$currentTime]);
                               })
                               ->orWhere(function($q4) use ($currentTime) {
                                   $q4->where('session_type', 'checkout')
                                      ->whereNotNull('checkout_start_time')
                                      ->whereRaw("TIME(checkout_start_time) > ?", [$currentTime]);
                               });
                           });
                    })
                    // Future dates
                    ->orWhere('date', '>', $today);
                })
                ->orderBy('date')
                ->orderBy('session_type')
                ->first();
        }
        
        // Generate QR for the selected session
        $sessionsWithQR = collect();
        if ($activeSession && $activeSession->unique_code) {
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrSvg = $writer->writeString($activeSession->unique_code);
            
            $sessionsWithQR->push([
                'id' => $activeSession->id,
                'date' => $activeSession->date,
                'session_type' => $activeSession->session_type,
                'checkin_start_time' => $activeSession->checkin_start_time,
                'checkin_end_time' => $activeSession->checkin_end_time,
                'checkout_start_time' => $activeSession->checkout_start_time,
                'checkout_end_time' => $activeSession->checkout_end_time,
                'unique_code' => $activeSession->unique_code,
                'qr_svg' => $qrSvg,
                'is_active_now' => $attendance->sessions()
                    ->where('id', $activeSession->id)
                    ->where('date', $today)
                    ->where(function($q) use ($currentTime) {
                        $q->where(function($q2) use ($currentTime) {
                            $q2->where('session_type', 'checkin')
                               ->whereRaw("TIME(?) BETWEEN TIME(checkin_start_time) AND TIME(checkin_end_time)", [$currentTime]);
                        })
                        ->orWhere(function($q2) use ($currentTime) {
                            $q2->where('session_type', 'checkout')
                               ->whereRaw("TIME(?) BETWEEN TIME(checkout_start_time) AND TIME(checkout_end_time)", [$currentTime]);
                        });
                    })
                    ->exists(),
            ]);
        }
        
        return view('attendance.qrcode', compact('attendance', 'sessionsWithQR'));
    }

    public function searchParticipant(Request $request, Attendance $attendance)
    {
        $request->validate([
            'ic' => 'nullable|string',
            'passport' => 'nullable|string',
            'id_type' => 'required|in:ic,passport',
        ]);

        $idType = $request->id_type;
        $query = \App\Models\Participant::where('event_id', $attendance->event_id);

        if ($idType === 'ic') {
            $normalizedIc = preg_replace('/\D+/', '', $request->ic);
            $query->whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc]);
        } else {
            $normalizedPass = strtolower(preg_replace('/\s+/', '', $request->passport));
            $query->whereRaw("LOWER(REPLACE(passport_no, ' ', '')) = ?", [$normalizedPass]);
        }

        $participant = $query->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak dijumpai untuk event ini.'
            ], 404);
        }

        // Get attendance history for this participant & attendance
        // Group sessions by date to get unique dates
        $allSessions = $attendance->sessions()->orderBy('date')->get();
        $dateGroups = $allSessions->groupBy('date');
        $history = [];

        foreach ($dateGroups as $date => $sessionsForDate) {
            $checkinSession = $sessionsForDate->where('session_type', 'checkin')->first();
            $checkoutSession = $sessionsForDate->where('session_type', 'checkout')->first();

            $checkinRecord = null;
            $checkoutRecord = null;

            if ($checkinSession) {
                $checkinRecord = \App\Models\AttendanceRecord::where('attendance_session_id', $checkinSession->id)
                    ->where('participant_id', $participant->id)
                    ->first();
            }
            if ($checkoutSession) {
                $checkoutRecord = \App\Models\AttendanceRecord::where('attendance_session_id', $checkoutSession->id)
                    ->where('participant_id', $participant->id)
                    ->first();
            }

            $sessionDateObj = \Carbon\Carbon::parse($date);
            $isPast = $sessionDateObj->lt(now()->startOfDay());
            $isToday = $sessionDateObj->isToday();

            // Determine check-in status
            $checkinTime = $checkinRecord ? $checkinRecord->checkin_time : null;
            $checkoutTime = $checkoutRecord ? $checkoutRecord->checkout_time : null;
            
            $checkinStatus = 'Pending';
            if ($isPast && !$checkinTime) {
                // If has checkout but no checkin, mark as Late
                if ($checkoutTime) {
                    $checkinStatus = 'Late';
                } else {
                    $checkinStatus = 'Absent';
                }
            } elseif ($checkinTime && $checkinSession) {
                $checkinTimeObj = \Carbon\Carbon::parse($checkinTime);
                $checkinEnd = \Carbon\Carbon::parse($checkinSession->date . ' ' . $checkinSession->checkin_end_time);
                $checkinStatus = $checkinTimeObj->lte($checkinEnd) ? 'On Time' : 'Late';
            } elseif ($isToday && $checkinSession) {
                // Check if check-in window has passed
                $checkinEndTime = \Carbon\Carbon::parse($checkinSession->date . ' ' . $checkinSession->checkin_end_time);
                if (now()->gt($checkinEndTime)) {
                    // Window passed, check if has checkout
                    if ($checkoutTime) {
                        $checkinStatus = 'Late';
                    } else {
                        $checkinStatus = 'Absent';
                    }
                } else {
                    $checkinStatus = 'Pending';
                }
            }

            // Determine check-out status
            $checkoutTime = $checkoutRecord ? $checkoutRecord->checkout_time : null;
            $checkoutStatus = 'Pending';
            $hasCheckout = (bool) $checkoutSession;
            if ($isPast && !$checkoutTime && $hasCheckout) {
                $checkoutStatus = 'Absent';
            } elseif ($checkoutTime && $checkoutSession) {
                $checkoutTimeObj = \Carbon\Carbon::parse($checkoutTime);
                $checkoutStart = \Carbon\Carbon::parse($checkoutSession->date . ' ' . $checkoutSession->checkout_start_time);
                $checkoutStatus = $checkoutTimeObj->gte($checkoutStart) ? 'On Time' : 'Early';
            } elseif ($isToday && $checkoutSession) {
                // Check if checkout window has passed
                $checkoutEndTime = \Carbon\Carbon::parse($checkoutSession->date . ' ' . $checkoutSession->checkout_end_time);
                if (now()->gt($checkoutEndTime)) {
                    $checkoutStatus = 'Absent';
                } else {
                    $checkoutStatus = 'Pending';
                }
            }

            $history[] = [
                'date' => \Carbon\Carbon::parse($date)->format('d F Y'),
                'checkin_time' => $checkinTime ? \Carbon\Carbon::parse($checkinTime)->format('h:i:s a') : null,
                'checkin_status' => $checkinStatus,
                'checkout_time' => $checkoutTime ? \Carbon\Carbon::parse($checkoutTime)->format('h:i:s a') : null,
                'checkout_status' => $checkoutStatus,
                'has_checkout' => $hasCheckout,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'identity_card' => $participant->identity_card,
                    'passport_no' => $participant->passport_no,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'organization' => $participant->organization,
                ],
                'history' => $history,
            ]
        ]);
    }

    public function checkinManual(Request $request, Attendance $attendance)
    {
        try {
            $request->validate(['participant_id' => 'required|exists:participants,id']);
            $participantId = $request->participant_id;

        // Verify participant belongs to this event
        $participant = \App\Models\Participant::where('id', $participantId)
            ->where('event_id', $attendance->event_id)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not valid for this event.'
            ], 400);
        }

        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // Find active session for today and current time
        $session = $attendance->sessions()
            ->where('date', $today)
            ->where(function($q) use ($currentTime) {
                // Check-in window
                $q->where(function($q2) use ($currentTime) {
                    $q2->where('session_type', 'checkin')
                       ->whereNotNull('checkin_start_time')
                       ->whereRaw("TIME(?) BETWEEN TIME(checkin_start_time) AND TIME(checkin_end_time)", [$currentTime]);
                })
                // Checkout window
                ->orWhere(function($q2) use ($currentTime) {
                    $q2->where('session_type', 'checkout')
                       ->whereNotNull('checkout_start_time')
                       ->whereRaw("TIME(?) BETWEEN TIME(checkout_start_time) AND TIME(checkout_end_time)", [$currentTime]);
                });
            })
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active check-in/check-out window for today at this time.'
            ], 400);
        }

        // Check if already checked in/out for this session
        $existingRecord = \App\Models\AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('participant_id', $participantId)
            ->first();

        if ($session->session_type === 'checkin') {
            if ($existingRecord && $existingRecord->checkin_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant already checked in for this session.'
                ], 400);
            }
            // Create or update check-in
            if ($existingRecord) {
                $existingRecord->update(['checkin_time' => $now, 'status' => 'present']);
                $record = $existingRecord;
            } else {
                $record = \App\Models\AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'participant_id' => $participantId,
                    'attendance_session_id' => $session->id,
                    'checkin_time' => $now,
                    'timestamp' => $now,
                    'status' => 'present',
                    'scanned_by_device' => 'manual_web',
                ]);
            }
            $actionType = 'Check-in';
        } else {
            // Checkout
            if ($existingRecord && $existingRecord->checkout_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant already checked out for this session.'
                ], 400);
            }
            
            if ($existingRecord) {
                // Update existing record with checkout
                $existingRecord->update(['checkout_time' => $now]);
                $record = $existingRecord;
            } else {
                // Create new record with checkout only (checkin akan late/absent)
                $record = \App\Models\AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'participant_id' => $participantId,
                    'attendance_session_id' => $session->id,
                    'checkin_time' => null,
                    'checkout_time' => $now,
                    'timestamp' => $now,
                    'status' => 'present', // Present but late for checkin (display logic will show Late)
                    'scanned_by_device' => 'manual_web',
                ]);
            }
            $actionType = 'Check-out';
        }

            return response()->json([
                'success' => true,
                'message' => $actionType . ' successful for ' . $participant->name . '!',
                'data' => [
                    'record_id' => $record->id,
                    'checkin_time' => $record->checkin_time,
                    'checkout_time' => $record->checkout_time,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Manual check-in error: ' . $e->getMessage(), [
                'attendance_id' => $attendance->id,
                'participant_id' => $request->participant_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        // Get all events (filtered by role)
        if (auth()->user()->hasRole('Administrator')) {
            $events = \App\Models\Event::orderBy('start_date')->get(['id', 'name']);
        } else {
            $events = \App\Models\Event::where('user_id', auth()->id())->orderBy('start_date')->get(['id', 'name']);
        }

        // Get selected event and session from request
        $selectedEventId = $request->input('event_id') ?? ($events->first()->id ?? null);
        $sessions = [];
        $selectedSessionId = $request->input('session_id');
        $participants = [];

        if ($selectedEventId) {
            // Get sessions for selected event (no change needed, already filtered by event)
            $sessions = \App\Models\AttendanceSession::whereHas('attendance', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            })->orderBy('date')->get(['id', 'date', 'checkin_start_time', 'checkin_end_time', 'checkout_start_time', 'checkout_end_time']);

            // Default to first session if not selected
            if (!$selectedSessionId && $sessions->count()) {
                $selectedSessionId = $sessions->first()->id;
            }

            // Get participants for selected session
            if ($selectedSessionId) {
                $query = \DB::table('attendance_records')
                    ->join('participants', 'attendance_records.participant_id', '=', 'participants.id')
                    ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
                    ->where('attendance_sessions.id', $selectedSessionId)
                    ->select(
                        'attendance_records.id as record_id',
                        'participants.id as participant_id',
                        'participants.name',
                        'attendance_records.status'
                    );
                
                // Get paginated results with per_page parameter
                $perPage = $request->get('per_page', 10);
                $participants = $query->paginate($perPage);
            } else {
                $participants = collect([]);
            }
        }

        // Format sessions for dropdown
        $sessionsArray = collect($sessions)->map(function($s) {
            return [
                'id' => $s->id,
                'name' => ($s->date ? date('d M Y', strtotime($s->date)) : '-') .
                    ' (' . ($s->checkin_start_time ?? '-') . ' - ' . ($s->checkin_end_time ?? '-') . ')',
            ];
        });

        return view('attendance.list', [
            'events' => $events,
            'sessions' => $sessionsArray,
            'participants' => $participants,
            'selectedEventId' => $selectedEventId,
            'selectedSessionId' => $selectedSessionId,
        ]);
    }

    public function apiSessions(Request $request)
    {
        $eventId = $request->input('event_id');
        $sessions = [];
        if ($eventId) {
            $sessions = \App\Models\AttendanceSession::whereHas('attendance', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })->orderBy('date')->get(['id', 'date', 'checkin_start_time', 'checkin_end_time', 'checkout_start_time', 'checkout_end_time']);
        }
        $sessionsArray = collect($sessions)->map(function($s) {
            return [
                'id' => $s->id,
                'name' => ($s->date ? date('d M Y', strtotime($s->date)) : '-') .
                    ' (' . ($s->checkin_start_time ?? '-') . ' - ' . ($s->checkin_end_time ?? '-') . ')',
            ];
        })->values();
        return response()->json($sessionsArray);
    }

    public function apiParticipants(Request $request)
    {
        $sessionId = $request->input('session_id');
        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(1, (int)$request->input('per_page', 10));
        $participants = collect();
        $total = 0;
        if ($sessionId) {
            $query = \DB::table('attendance_records')
                ->join('participants', 'attendance_records.participant_id', '=', 'participants.id')
                ->join('attendance_sessions', 'attendance_records.attendance_id', '=', 'attendance_sessions.attendance_id')
                ->where('attendance_sessions.id', $sessionId)
                ->select(
                    'attendance_records.id as record_id',
                    'participants.id as participant_id',
                    'participants.name',
                    'attendance_records.checkin_time',
                    'attendance_records.checkout_time',
                    'attendance_records.status'
                );
            $search = $request->input('search');
            $statusFilter = $request->input('status');
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('participants.name', 'like', "%$search%")
                      ->orWhere('participants.identity_card', 'like', "%$search%")
                      ->orWhere('participants.passport_no', 'like', "%$search%")
                      ->orWhere('participants.id_passport', 'like', "%$search%")
                      ;
                });
            }
            // Apply status filter
            if ($statusFilter) {
                $query->where('attendance_records.status', $statusFilter);
            }
            $total = $query->count();
            $participants = $query->skip(($page-1)*$perPage)->take($perPage)->get();
            // Map to match frontend expectation
            $participants = $participants->map(function($p) {
                return [
                    'record_id' => $p->record_id,
                    'participant_id' => $p->participant_id,
                    'name' => $p->name,
                    'time' => $p->checkin_time,
                    'checkout_time' => $p->checkout_time,
                    'status' => $p->status,
                ];
            });
        }
        return response()->json([
            'data' => $participants,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function archive(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with('event')->where('status', 'archived');

        if ($user->hasRole('Organizer')) {
            $eventIds = \App\Models\Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }
        // Administrator sees all archives

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('event', function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->where('date', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('date', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('date', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('date', '<', $today->format('Y-m-d'));
                    break;
            }
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $attendances = $query->orderBy('date', 'desc')->orderBy('start_time', 'asc')->paginate($perPage);

        // Get events for filter dropdown
        if ($user->hasRole('Administrator')) {
            $events = \App\Models\Event::orderBy('name')->get();
        } else {
            $events = \App\Models\Event::where('user_id', $user->id)->orderBy('name')->get();
        }

        return view('attendance.archive', compact('attendances', 'events'));
    }

    public function archiveAction($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = 'archived';
        $attendance->save();
        return redirect()->route('attendance.index')->with('success', 'Attendance archived successfully.');
    }

    public function unarchiveAction($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = 'active';
        $attendance->save();
        return redirect()->route('attendance.archive')->with('success', 'Attendance unarchived successfully.');
    }
}
