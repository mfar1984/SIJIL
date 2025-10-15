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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participant = PwaParticipant::create([
            'name' => $request->name,
            'email' => $request->email,
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
        $participant = $request->user();

        $registrations = $participant->eventRegistrations()
                                    ->with(['event' => function($query) {
                                        $query->select('id', 'name as title', 'description', 'start_date as date', 'start_time as time', 'location', 'user_id as organizer_id');
                                    }])
                                    ->orderBy('registration_date', 'desc')
                                    ->get();

        $events = $registrations->map(function($registration) {
            return [
                'id' => $registration->event->id,
                'title' => $registration->event->title,
                'description' => $registration->event->description,
                'date' => $registration->event->date,
                'time' => $registration->event->time,
                'location' => $registration->event->location,
                'registration_date' => $registration->registration_date,
                'attendance_date' => $registration->attendance_date,
                'status' => $registration->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events
            ]
        ]);
    }

    /**
     * Get participant certificates
     */
    public function getCertificates(Request $request)
    {
        $participant = $request->user();

        $certificates = $participant->certificates()
                                   ->with('event')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'certificates' => $certificates
            ]
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
}
