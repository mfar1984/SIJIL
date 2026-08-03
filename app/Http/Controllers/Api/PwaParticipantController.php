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
            // No length rule. Validating the length of a password being *checked*
            // rejects it before the credentials are compared, which both tells an
            // attacker the minimum length and locks out anyone whose existing
            // password is shorter than the current policy.
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Not filtered on status here: a banned account must be told it is banned
        // rather than shown "invalid credentials", which would look like a typo and
        // send them round in circles.
        $participant = PwaParticipant::whereRaw('LOWER(email) = ?', [strtolower(trim($request->email))])
                                    ->first();

        // The lockout columns login_attempts and locked_until have existed all
        // along, together with an isLocked() helper, and nothing ever incremented
        // the counter or read the helper. The only protection was the route's
        // 8 requests a minute, which does nothing against a slow attempt at one
        // password every ten seconds.
        if ($participant && $participant->isLocked()) {
            \App\Support\SecurityPolicy::audit('lockout', 'Participant app login blocked - account locked', [
                'email' => $participant->email,
                'ip_address' => $request->ip(),
                'locked_until' => $participant->locked_until->toDateTimeString(),
            ], $participant);

            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Try again in '
                    . max(1, $participant->locked_until->diffInMinutes(now()) + 1) . ' minute(s).',
            ], 429);
        }

        if (!$participant || !Hash::check($request->password, $participant->password)) {
            if ($participant) {
                $this->recordFailedParticipantLogin($participant, $request);
            }

            \App\Support\SecurityPolicy::audit('failed_login', 'Participant app login failed', [
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'account_exists' => (bool) $participant,
            ], $participant);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($participant->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been suspended. Please contact the event organizer.',
            ], 403);
        }

        if ($participant->is_active === false || $participant->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active. Please contact the event organizer.',
            ], 403);
        }

        // Create token. The lifetime comes from the Security tab; zero keeps the
        // original behaviour of never expiring.
        $lifetimeDays = \App\Support\SecurityPolicy::apiTokenLifetimeDays();

        $token = $participant->createToken(
            'pwa-token',
            ['*'],
            $lifetimeDays > 0 ? now()->addDays($lifetimeDays) : null
        )->plainTextToken;

        // Record the sign-in and clear any lockout. The columns already existed
        // but nothing ever wrote to them, so the admin side could not tell who
        // actually uses the app.
        $participant->forceFill([
            'last_login_at' => now(),
            'login_attempts' => 0,
            'locked_until' => null,
        ])->saveQuietly();

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
     * Count a failed sign-in against a participant account and lock it once the
     * configured number of attempts is reached.
     *
     * Uses the same Max Login Attempts and Lockout Duration as the backend, so
     * the Security tab describes both sign-in paths rather than only one.
     */
    private function recordFailedParticipantLogin(PwaParticipant $participant, Request $request): void
    {
        $attempts = (int) $participant->login_attempts + 1;
        $limit = \App\Support\SecurityPolicy::maxLoginAttempts();

        $attributes = ['login_attempts' => $attempts];

        if ($attempts >= $limit) {
            $attributes['locked_until'] = now()->addSeconds(\App\Support\SecurityPolicy::lockoutSeconds());
            $attributes['login_attempts'] = 0;

            \App\Support\SecurityAlert::send('Participant account locked', [
                'Account' => $participant->email,
                'IP address' => (string) $request->ip(),
                'Attempts allowed' => (string) $limit,
            ]);
        }

        $participant->forceFill($attributes)->saveQuietly();
    }

    /*
     * The disabled register() method that sat here has been removed.
     *
     * It was already unroutable, having been made private when the open sign-up
     * endpoint was withdrawn, and its own comment set out what it would need
     * before it could safely come back: proof that the caller controls the email
     * address, because events and certificates are matched by address and so
     * creating an account for one hands over whatever that address may see.
     *
     * That requirement is now met elsewhere. Account creation during registration
     * lives in App\Http\Controllers\Api\EventRegistrationGateController, which
     * refuses an address that already has an account, demands the registration
     * token of an open event, and issues no API token at all. This copy minted a
     * working token on the spot, carried a third private username generator, and
     * would have been the wrong thing to reinstate.
     */

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
                'gender' => $participant->gender,
                'race' => $participant->race,
                'date_of_birth' => $participant->date_of_birth,
                'identity_card' => $participant->identity_card,
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
            'gender' => 'nullable|string|in:male,female,other',
            'race' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'identity_card' => 'nullable|string|max:20',
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
            'gender', 'race', 'date_of_birth', 'identity_card',
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

        // Debug logging
        \Log::info('PWA Certificate Lookup', [
            'pwa_id' => $pwa->id,
            'pwa_email' => $pwa->email,
            'pwa_identity_card' => $pwa->identity_card,
            'participant_ids_found' => $participantIds->toArray(),
        ]);

        $certificates = \App\Models\Certificate::whereIn('participant_id', $participantIds->all())
                                   ->with('event')
                                   ->orderBy('generated_at', 'desc')
                                   ->get();

        \Log::info('Certificates Found', [
            'count' => $certificates->count(),
            'certificate_ids' => $certificates->pluck('id')->toArray(),
        ]);

        $data = $certificates->map(function($cert) {
            return [
                'id' => $cert->id,
                'title' => 'Certificate of Attendance', // Default title
                'event_name' => $cert->event->name ?? 'Unknown Event',
                'certificate_number' => $cert->certificate_number,
                'issued_date' => $cert->generated_at,
                'pdf_file' => $cert->pdf_file,
                'description' => null, // Can add description field to certificates table if needed
                // The public page a recipient can open to confirm this certificate is
                // real. Without it the app had no URL worth sharing and fell back to
                // its own certificates screen, which shows the recipient their own
                // certificates rather than this one.
                'verify_url' => route('certificate.verify', ['number' => $cert->certificate_number]),
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
    /**
     * No longer routed, and private so it cannot be reached.
     *
     * This answered any IC or passport number with a full profile - name, email,
     * phone, IC, passport, address, date of birth, gender, race, job title - with
     * no authentication. Malaysian IC numbers encode a birth date and a state code,
     * so the keyspace is small enough to walk. Nothing in the app called it.
     *
     * If a pre-fill helper is wanted on a registration form, it should return only
     * whether a record exists, never its contents.
     */
    private function lookupByIdentity(Request $request)
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
        $participant = PwaParticipant::whereRaw('LOWER(email) = ?', [strtolower(trim($request->email))])->first();

        if (!$participant) {
            // Email not found - return error (user-friendly feedback)
            return response()->json([
                'success' => false,
                'message' => 'Email address not found in our system. Please check and try again.'
            ], 404);
        }

        // No point issuing a password to an account that cannot sign in.
        if ($participant->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been suspended. Please contact the event organizer.',
            ], 403);
        }

        // Generate new password using the configured length and complexity
        $newPassword = \App\Support\PwaPassword::generate();
        
        $participant->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now()
        ]);

        // Sent through the shared mailer. What stood here was a second copy of
        // everything PwaMailer does - pick the delivery config, find the template,
        // substitute @{{vars}}, send - and the copy had drifted: it addressed
        // people to url('/pwa/login'), which is not a route in this application,
        // and it recorded nothing in pwa_email_logs, so resets were invisible on
        // the email reporting screens that count welcome messages.
        //
        // No sender is named, which makes PwaMailer use the Administrator's
        // configuration. That is deliberate: a reset is requested from the app's
        // own sign-in screen, where no organizer is involved.
        $mail = \App\Support\PwaMailer::send(
            type: 'password_reset',
            participant: $participant,
            vars: ['password' => $newPassword],
            fallback: [
                'subject' => 'Password Reset - E-Certificate',
                'content' => '<p><strong>Dear @{{name}},</strong></p>'
                    . '<p>Your password has been reset.</p>'
                    . '<div style="background-color:#f9fafb;padding:12px;border-radius:4px;margin:16px 0">'
                    . '<p style="font-size:14px;margin:0 0 6px"><strong>Email:</strong> @{{email}}</p>'
                    . '<p style="font-size:14px;margin:0"><strong>New password:</strong> @{{password}}</p>'
                    . '</div>'
                    . '<p>Sign in at @{{login_url}} and change your password straight away.</p>'
                    . '<p style="margin-top:16px;font-size:12px;color:#6b7280">'
                    . 'If you did not request this, contact us at @{{support_email}}</p>',
            ]
        );

        // Failures are logged inside PwaMailer rather than surfaced. The password
        // has already been changed by this point, so reporting a delivery problem
        // to an unauthenticated caller would tell them the address exists without
        // helping the person who actually owns it.
        return response()->json([
            'success' => true,
            'message' => 'If the email exists, a password reset has been sent.',
        ]);
    }
}
