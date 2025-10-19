<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PwaParticipant;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PwaParticipantController extends Controller
{
    /**
     * Participant login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participant = PwaParticipant::where('email', $request->email)
                                    ->where('status', 'active')
                                    ->first();

        if (!$participant || !Hash::check($request->password, $participant->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create token
        $token = $participant->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'organization' => $participant->organization,
            ]
        ]);
    }

    /**
     * Participant registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pwa_participants',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'username' => 'sometimes|string|max:255|unique:pwa_participants,username',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ensure username is provided to satisfy NOT NULL schema
        $username = $request->input('username');
        if (!$username) {
            $emailPrefix = strstr($request->email, '@', true) ?: '';
            $base = strtolower(preg_replace('/[^a-z0-9]/', '', $emailPrefix));
            if (!$base) {
                $base = strtolower(preg_replace('/[^a-z0-9]/', '', Str::slug($request->name)));
            }
            if (!$base) {
                $base = 'user';
            }

            $candidate = $base;
            $suffix = 1;
            while (PwaParticipant::where('username', $candidate)->exists()) {
                // avoid infinite loops in case of heavy collisions
                if ($suffix > 50) {
                    $candidate = $base . Str::lower(Str::random(4));
                    break;
                }
                $candidate = $base . $suffix;
                $suffix++;
            }
            $username = $candidate;
        }

        $participant = PwaParticipant::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'organization' => $request->organization,
            'status' => 'active',
        ]);

        $token = $participant->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'username' => $participant->username,
                'phone' => $participant->phone,
                'organization' => $participant->organization,
            ]
        ], 201);
    }

    /**
     * Get participant profile
     */
    public function profile(Request $request)
    {
        $participant = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'organization' => $participant->organization,
                'job_title' => $participant->job_title,
                'address1' => $participant->address1,
                'address2' => $participant->address2,
                'city' => $participant->city,
                'state' => $participant->state,
                'postcode' => $participant->postcode,
                'country' => $participant->country,
            ]
        ]);
    }

    /**
     * Update participant profile
     */
    public function updateProfile(Request $request)
    {
        $participant = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participant->update($request->only([
            'name', 'phone', 'organization', 'job_title',
            'address1', 'address2', 'city', 'state', 'postcode', 'country'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $participant->fresh()
        ]);
    }

    /**
     * Get all events for participant
     */
    public function getEvents(Request $request)
    {
        $pwa = $request->user();

        // Get ALL participants with same email (across all organizers)
        $participantIds = collect();
        
        // Search by email - THIS IS CRITICAL for multi-organizer support
        $byEmail = \App\Models\Participant::where('email', $pwa->email)->pluck('id');
        $participantIds = $participantIds->merge($byEmail);
        
        // Also search by IC if available
        if (!empty($pwa->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $pwa->identity_card);
            $byIc = \App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id');
            $participantIds = $participantIds->merge($byIc);
        }
        
        $participantIds = $participantIds->unique()->values();

        if ($participantIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [ 'events' => [] ]
            ]);
        }

        // Get all participants (one per event, potentially from different organizers)
        $participants = \App\Models\Participant::whereIn('id', $participantIds->all())
            ->with(['event', 'event.user']) // eager load event and organizer
            ->get();

        // Get attendance records for all participants
        $attendanceRecords = \App\Models\AttendanceRecord::whereIn('participant_id', $participantIds->all())
            ->with('attendanceSession')
            ->get()
            ->groupBy('participant_id');

        $eventsData = [];
        
        foreach ($participants as $participant) {
            $event = $participant->event;
            
            if (!$event) {
                continue;
            }

            // Dedupe by event ID (in case same email registered multiple times for same event)
            if (isset($eventsData[$event->id])) {
                continue;
            }

            // Check attendance from attendance_records via sessions
            $attendance = $attendanceRecords->get($participant->id);
            $hasAttended = false;
            $attendanceDate = null;
            
            if ($attendance) {
                foreach ($attendance as $attRecord) {
                    if ($attRecord->attendanceSession && $attRecord->attendanceSession->event_id == $event->id) {
                        $hasAttended = true;
                        $attendanceDate = $attRecord->checked_in_at ?? $attRecord->created_at;
                        break;
                    }
                }
            }

            $eventsData[$event->id] = [
                'id' => $event->id,
                'title' => $event->name, // Column is 'name' not 'title'
                'description' => $event->description,
                'date' => $event->start_date ? $event->start_date->format('Y-m-d') : null, // Return date string only
                'end_date' => $event->end_date ? $event->end_date->format('Y-m-d') : null, // Return date string only
                'start_time' => $event->start_time ? substr($event->start_time, 0, 5) : null,
                'end_time' => $event->end_time ? substr($event->end_time, 0, 5) : null,
                'location' => $event->location,
                'organizer' => $event->organizer ?? ($event->user ? $event->user->name : null),
                'registration_date' => $participant->created_at->toISOString(),
                'attendance_date' => $attendanceDate ? $attendanceDate->toISOString() : null,
                'status' => $hasAttended ? 'attended' : 'registered',
            ];
        }

        // Sort by date (newest first)
        $eventsList = collect($eventsData)->sortByDesc('date')->values()->all();

        return response()->json([
            'success' => true,
            'data' => [ 
                'events' => $eventsList,
                'total' => count($eventsList)
            ]
        ]);
    }

    /**
     * Get participant certificates
     */
    public function getCertificates(Request $request)
    {
        $pwa = $request->user();
        // Prefer IC (normalised) + union emel
        $participantIds = collect();
        if (!empty($pwa->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $pwa->identity_card);
            $participantIds = $participantIds->merge(\App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id'));
        }
        $participantIds = $participantIds->merge(\App\Models\Participant::where('email', $pwa->email)->pluck('id'))->unique()->values();

        $certificates = \App\Models\Certificate::whereIn('participant_id', $participantIds->all())
                                   ->with('event')
                                   ->orderBy('generated_at', 'desc')
                                   ->get();

        $data = $certificates->map(function($cert) {
            return [
                'id' => $cert->id,
                'title' => 'Certificate of Attendance', // Default title
                'event_name' => $cert->event->name ?? 'Unknown Event',
                'certificate_number' => $cert->certificate_number,
                'issued_date' => $cert->generated_at,
                'pdf_file' => $cert->pdf_file,
                'description' => null, // Can add description field to certificates table if needed
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'certificates' => $data
            ]
        ]);
    }

    /**
     * Download certificate PDF
     */
    public function downloadCertificate(Request $request, $certificateId)
    {
        $participant = $request->user();

        // Build list of participant IDs associated to this PWA user (IC + email),
        // same logic as getCertificates so download works for aggregated participants
        $participantIds = collect();
        if (!empty($participant->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $participant->identity_card);
            $participantIds = $participantIds->merge(\App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id'));
        }
        $participantIds = $participantIds->merge(\App\Models\Participant::where('email', $participant->email)->pluck('id'))->unique()->values();

        // Fallback to related_participant_id if available
        if ($participant->related_participant_id) {
            $participantIds = $participantIds->merge([$participant->related_participant_id])->unique()->values();
        }

        $certificate = \App\Models\Certificate::where('id', $certificateId)
            ->whereIn('participant_id', $participantIds->all())
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found or you do not have permission to access it'
            ], 404);
        }

        if (!$certificate->pdf_file) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate PDF has not been generated yet'
            ], 404);
        }

        // Resolve file path with fallbacks
        $rel = ltrim($certificate->pdf_file, '/');
        if ($rel && substr($rel, -4) === '.pdf' && strpos($rel, 'certificates/') !== 0 && strpos($rel, 'certificate-') === 0) {
            $rel = 'certificates/' . basename($rel);
        }

        $candidatePaths = [
            storage_path('app/public/' . $rel),
            public_path('storage/' . $rel),
            public_path($rel),
        ];

        $filePath = null;
        foreach ($candidatePaths as $p) {
            if ($p && file_exists($p)) { $filePath = $p; break; }
        }

        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate PDF file not found. Please contact support.'
            ], 404);
        }
        $fileName = ($certificate->certificate_number ?? 'certificate') . '.pdf';

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Check in for an event
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participant = $request->user();
        $event = Event::find($request->event_id);

        // Verify QR code (you can implement your own QR validation logic)
        if ($event->getQRCode() !== $request->qr_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code'
            ], 400);
        }

        // Check if already registered
        $registration = $participant->eventRegistrations()
                                   ->where('event_id', $request->event_id)
                                   ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Not registered for this event'
            ], 400);
        }

        if ($registration->status === 'attended') {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in for this event'
            ], 400);
        }

        // Check in
        $registration->update([
            'attendance_date' => now(),
            'status' => 'attended'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => [
                'event_title' => $event->name,
                'check_in_time' => now(),
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participant = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $participant->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Update password
        $participant->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get attendance history
     */
    public function getAttendanceHistory(Request $request)
    {
        $pwa = $request->user();

        // Attendance by IC (normalised) + emel
        $participantIds = collect();
        if (!empty($pwa->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $pwa->identity_card);
            $participantIds = $participantIds->merge(\App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id'));
        }
        $participantIds = $participantIds->merge(\App\Models\Participant::where('email', $pwa->email)->pluck('id'))->unique()->values();

        $records = \App\Models\AttendanceRecord::whereIn('participant_id', $participantIds->all())
            ->with(['attendance.event', 'attendanceSession'])
            ->orderBy('created_at', 'desc')
            ->get();

        $history = $records->map(function($record) {
            $event = $record->attendance->event ?? null;
            $session = $record->attendanceSession ?? null;
            
            return [
                'id' => $record->id,
                'event_name' => $event ? $event->name : 'Unknown Event',
                'event_date' => $session ? $session->date : ($event ? $event->start_date : null),
                'location' => $event ? $event->location : null,
                'checkin_time' => $record->checkin_time,
                'checkout_time' => $record->checkout_time,
                'checkin_lat' => $record->checkin_lat ?? null,
                'checkin_lng' => $record->checkin_lng ?? null,
                'checkout_lat' => $record->checkout_lat ?? null,
                'checkout_lng' => $record->checkout_lng ?? null,
                'status' => $record->status,
                'scanned_by_device' => $record->scanned_by_device,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Lookup by Identity Card (IC) or Passport to prefill registration/login flow
     */
    public function lookupByIdentity(Request $request)
    {
        $request->validate([
            'ic' => 'nullable|string',
            'passport' => 'nullable|string',
            'id_type' => 'nullable|in:ic,passport',
        ]);

        $ic = $request->get('ic');
        $passport = $request->get('passport');
        $idType = $request->get('id_type');

        if (!$ic && !$passport) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide IC or Passport'
            ], 422);
        }

        $participantsQuery = \App\Models\Participant::query();
        if ($ic || $idType === 'ic') {
            $normalizedIc = preg_replace('/\D+/', '', (string) $ic);
            $participantsQuery->whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc]);
        } elseif ($passport || $idType === 'passport') {
            $normalizedPass = strtolower(preg_replace('/\s+/', '', (string) $passport));
            $participantsQuery->whereRaw("LOWER(REPLACE(passport_no, ' ', '')) = ?", [$normalizedPass]);
        }

        $participants = $participantsQuery->orderBy('updated_at', 'desc')->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => false,
                    'emails' => [],
                    'last_participant' => null,
                ]
            ]);
        }

        $last = $participants->first();
        $emails = $participants->pluck('email')->filter()->unique()->values()->all();

        // Check if a PWA profile exists for any of these emails
        $pwa = \App\Models\PwaParticipant::whereIn('email', $emails)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => true,
                'emails' => $emails,
                'has_pwa' => (bool) $pwa,
                'last_participant' => [
                    'name' => $last->name,
                    'email' => $last->email,
                    'phone' => $last->phone,
                    'identity_card' => $last->identity_card,
                    'passport_no' => $last->passport_no,
                    'organization' => $last->organization,
                    'address1' => $last->address1,
                    'address2' => $last->address2,
                    'state' => $last->state,
                    'city' => $last->city,
                    'postcode' => $last->postcode,
                    'country' => $last->country,
                    'gender' => $last->gender,
                    'date_of_birth' => $last->date_of_birth,
                    'race' => $last->race,
                    'job_title' => $last->job_title,
                ],
            ]
        ]);
    }

    /**
     * Scan attendance QR code (session-based)
     */
    public function scanAttendance(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'device' => 'nullable|string',
        ]);

        $pwa = $request->user();
        $code = $request->code;

        // Find session by unique_code
        $session = \App\Models\AttendanceSession::where('unique_code', $code)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code or session not found.'
            ], 404);
        }

        $attendance = $session->attendance;
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found.'
            ], 404);
        }

        // Find participant by IC/email for this event
        $participantIds = collect();
        if (!empty($pwa->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $pwa->identity_card);
            $participantIds = $participantIds->merge(\App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id'));
        }
        $participantIds = $participantIds->merge(\App\Models\Participant::where('email', $pwa->email)->pluck('id'))->unique()->values();

        $participant = \App\Models\Participant::whereIn('id', $participantIds->all())
            ->where('event_id', $attendance->event_id)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered for this event.'
            ], 400);
        }

        $now = now();
        $sessionDate = \Carbon\Carbon::parse($session->date);
        $today = $now->toDateString();

        // Validate session is today
        if ($sessionDate->toDateString() !== $today) {
            return response()->json([
                'success' => false,
                'message' => 'This session is not active today.'
            ], 400);
        }

        // Validate time window
        $currentTime = $now->format('H:i:s');
        if ($session->session_type === 'checkin') {
            $startTime = $session->checkin_start_time;
            $endTime = $session->checkin_end_time;
            if ($currentTime < $startTime || $currentTime > $endTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in window is ' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . '. Current time is outside this window.'
                ], 400);
            }
        } else {
            $startTime = $session->checkout_start_time;
            $endTime = $session->checkout_end_time;
            if ($currentTime < $startTime || $currentTime > $endTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-out window is ' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . '. Current time is outside this window.'
                ], 400);
            }
        }

        // Find or create attendance record
        $record = \App\Models\AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($session->session_type === 'checkin') {
            if ($record && $record->checkin_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked in for this session.'
                ], 400);
            }
            if ($record) {
                $record->update([
                    'checkin_time' => $now,
                    'checkin_lat' => $request->lat,
                    'checkin_lng' => $request->lng,
                    'status' => 'present',
                ]);
            } else {
                $record = \App\Models\AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'participant_id' => $participant->id,
                    'attendance_session_id' => $session->id,
                    'checkin_time' => $now,
                    'checkin_lat' => $request->lat,
                    'checkin_lng' => $request->lng,
                    'timestamp' => $now,
                    'status' => 'present',
                    'scanned_by_device' => $request->device ?? 'pwa_web',
                ]);
            }
            $action = 'checkin';
            $time = $record->checkin_time;
        } else {
            // Checkout
            if ($record && $record->checkout_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked out for this session.'
                ], 400);
            }
            
            if ($record) {
                // Update existing record with checkout
                $record->update([
                    'checkout_time' => $now,
                    'checkout_lat' => $request->lat,
                    'checkout_lng' => $request->lng,
                ]);
            } else {
                // Create new record with checkout only (no prior check-in)
                $record = \App\Models\AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'participant_id' => $participant->id,
                    'attendance_session_id' => $session->id,
                    'checkin_time' => null,
                    'checkout_time' => $now,
                    'checkout_lat' => $request->lat,
                    'checkout_lng' => $request->lng,
                    'timestamp' => $now,
                    'status' => 'present',
                    'scanned_by_device' => $request->device ?? 'pwa_web',
                ]);
            }
            $action = 'checkout';
            $time = $record->checkout_time;
        }

        // Determine status (on time or late)
        $status = 'On Time';
        if ($action === 'checkin') {
            $checkinTime = \Carbon\Carbon::parse($time);
            $startTime = \Carbon\Carbon::parse($session->date . ' ' . $session->checkin_start_time);
            if ($checkinTime->greaterThan($startTime)) {
                $minutesLate = $checkinTime->diffInMinutes($startTime);
                $status = $minutesLate > 0 ? 'Late' : 'On Time';
            }
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($action) . ' successful!',
            'action' => $action,
            'time' => $time,
            'event_name' => $attendance->event->name ?? 'Event',
            'status' => $status,
            'location' => [
                'latitude' => $action === 'checkin' ? $record->checkin_lat : $record->checkout_lat,
                'longitude' => $action === 'checkin' ? $record->checkin_lng : $record->checkout_lng,
            ],
            'session' => [
                'date' => $session->date,
                'type' => $session->session_type,
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Reset password for PWA participant (from event registration)
     * Uses event organizer's SMTP configuration
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'event_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find PWA participant by email
        $participant = PwaParticipant::where('email', $request->email)->first();

        if (!$participant) {
            // Email not found - return error (user-friendly feedback)
            return response()->json([
                'success' => false,
                'message' => 'Email address not found in our system. Please check and try again.'
            ], 404);
        }

        // Generate new password
        $newPassword = \Illuminate\Support\Str::random(12);
        
        $participant->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now()
        ]);

        // For PWA reset password (login page), use Administrator config
        // For event registration reset, use organizer config
        $adminUser = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Administrator');
        })->first();

        // Attempt to send email using Administrator's DeliveryConfig
        try {
            if (!empty($participant->email) && $adminUser) {
                // Load Administrator's active email config
                $config = \App\Models\DeliveryConfig::getEmailConfig($adminUser->id);

                if ($config) {
                    $settings = $config->settings ?? [];
                    $fromName = $settings['from_name'] ?? 'SIJIL System';
                    $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';

                    // Configure mailer dynamically based on provider
                    switch ($config->provider) {
                        case 'smtp':
                            config([
                                'mail.default' => 'smtp',
                                'mail.mailers.smtp.host' => $settings['host'] ?? 'smtp.mailtrap.io',
                                'mail.mailers.smtp.port' => $settings['port'] ?? '2525',
                                'mail.mailers.smtp.encryption' => (($settings['encryption'] ?? null) === 'none') ? null : ($settings['encryption'] ?? null),
                                'mail.mailers.smtp.username' => $settings['username'] ?? '',
                                'mail.mailers.smtp.password' => $settings['password'] ?? '',
                                'mail.from.address' => $fromAddress,
                                'mail.from.name' => $fromName,
                            ]);
                            break;
                        case 'mailgun':
                            config([
                                'mail.default' => 'mailgun',
                                'services.mailgun.domain' => $settings['domain'] ?? '',
                                'services.mailgun.secret' => $settings['secret'] ?? '',
                                'services.mailgun.endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                                'mail.from.address' => $fromAddress,
                                'mail.from.name' => $fromName,
                            ]);
                            break;
                        case 'ses':
                            config([
                                'mail.default' => 'ses',
                                'services.ses.key' => $settings['key'] ?? '',
                                'services.ses.secret' => $settings['secret'] ?? '',
                                'services.ses.region' => $settings['region'] ?? 'us-east-1',
                                'mail.from.address' => $fromAddress,
                                'mail.from.name' => $fromName,
                            ]);
                            break;
                        case 'sendmail':
                            config([
                                'mail.default' => 'sendmail',
                                'mail.mailers.sendmail.path' => $settings['path'] ?? '/usr/sbin/sendmail -bs',
                                'mail.from.address' => $fromAddress,
                                'mail.from.name' => $fromName,
                            ]);
                            break;
                    }

                    // Find password reset template (global or admin scope)
                    $template = \App\Models\PwaEmailTemplate::query()
                        ->where('type', 'password_reset')
                        ->where('scope', 'global')
                        ->first();

                    $subject = 'Password Reset - E-Certificate';
                    $content = '<p><strong>Dear @{{name}},</strong></p><p>Your password has been reset for your PWA account.</p><div style="background-color: #f9fafb; padding: 12px; border-radius: 4px; margin: 16px 0;"><p style="font-size: 14px;"><strong>New Password:</strong> @{{password}}</p></div><p>Please login at @{{login_url}} and change your password immediately for security.</p><p style="margin-top: 16px; font-size: 12px; color: #6b7280;">If you did not request this reset, please contact us at @{{support_email}}</p>';
                    
                    if ($template) {
                        $subject = $template->subject ?: $subject;
                        $content = $template->content ?: $content;
                    }

                    // Prepare variables
                    $dataVars = [
                        'name' => $participant->name,
                        'email' => $participant->email,
                        'password' => $newPassword,
                        'pwa_link' => url('/pwa'),
                        'login_url' => url('/pwa/login'),
                        'support_email' => $fromAddress,
                        'organization' => 'E-Certificate System',
                    ];

                    // Replace variables
                    foreach ($dataVars as $key => $val) {
                        $subject = str_replace('@{{' . $key . '}}', $val, $subject);
                        $content = str_replace('@{{' . $key . '}}', $val, $content);
                    }

                    // Send email
                    \Illuminate\Support\Facades\Mail::html($content, function($message) use ($participant, $subject) {
                        $message->to($participant->email, $participant->name)
                                ->subject($subject);
                    });
                }
            }
        } catch (\Exception $e) {
            // Silent fail for security
        }

        return response()->json([
            'success' => true,
            'message' => 'If the email exists, a password reset has been sent.'
        ]);
    }
}
