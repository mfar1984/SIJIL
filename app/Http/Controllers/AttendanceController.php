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

        // Create sessions
        foreach ($request->sessions as $session) {
            $attendance->sessions()->create([
                'date' => $session['date'],
                'checkin_start_time' => $session['checkin_start_time'],
                'checkin_end_time' => $session['checkin_end_time'],
                'checkout_start_time' => $session['checkout_start_time'] ?? null,
                'checkout_end_time' => $session['checkout_end_time'] ?? null,
            ]);
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

        // Create new sessions
        foreach ($request->sessions as $session) {
            $attendance->sessions()->create([
                'date' => $session['date'],
                'checkin_start_time' => $session['checkin_start_time'],
                'checkin_end_time' => $session['checkin_end_time'],
                'checkout_start_time' => $session['checkout_start_time'] ?? null,
                'checkout_end_time' => $session['checkout_end_time'] ?? null,
            ]);
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
        // Generate QR code SVG for the unique_code
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($attendance->unique_code);
        return view('attendance.qrcode', compact('attendance', 'qrSvg'));
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
                    ->join('attendance_sessions', 'attendance_records.attendance_id', '=', 'attendance_sessions.attendance_id')
                    ->where('attendance_sessions.id', $selectedSessionId)
                    ->select(
                        'attendance_records.id as record_id',
                        'participants.id as participant_id',
                        'participants.name',
                        'participants.organization as ic',
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
                    'participants.organization as ic',
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
                      ->orWhere('participants.organization', 'like', "%$search%")
                      ->orWhere('participants.id', 'like', "%$search%")
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
                    'ic' => $p->ic,
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
