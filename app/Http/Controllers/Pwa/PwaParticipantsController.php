<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\PwaParticipant;
use App\Models\Participant;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PwaParticipantsController extends Controller
{
    /**
     * Display a listing of PWA participants with role-based filtering
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PwaParticipant::with(['events', 'certificates']);

        // Multi-tenant data filtering based on user role
        if ($user->hasRole('Administrator')) {
            // Administrator can see ALL participants from ALL organizers
        } else {
            // Organizer can only see participants from their own events
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $query->whereHas('events', function($q) use ($organizerEvents) {
                $q->whereIn('events.id', $organizerEvents);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $participants = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get counts for display
        if ($user->hasRole('Administrator')) {
            $totalParticipants = PwaParticipant::count();
            $totalEvents = Event::count();
        } else {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $totalParticipants = PwaParticipant::whereHas('events', function($q) use ($organizerEvents) {
                $q->whereIn('events.id', $organizerEvents);
            })->count();
            $totalEvents = Event::where('user_id', $user->id)->count();
        }

        return view('ecertificate.participants', compact('participants', 'totalParticipants', 'totalEvents'));
    }

    /**
     * Show the form for creating a new PWA participant
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get events based on user role
        if ($user->hasRole('Administrator')) {
            $events = Event::all();
        } else {
            $events = Event::where('user_id', $user->id)->get();
        }

        return view('ecertificate.participants.create', compact('events'));
    }

    /**
     * Store a newly created PWA participant
     */
    public function store(Request $request)
    {
        $registrationMethod = $request->input('registration_method', 'manual');

        switch ($registrationMethod) {
            case 'manual':
                return $this->storeManual($request);
            case 'auto_assign':
                return $this->storeAutoAssign($request);
            case 'bulk_import':
                return $this->storeBulkImport($request);
            default:
                return response()->json(['success' => false, 'message' => 'Invalid registration method'], 400);
        }
    }

    /**
     * Store manual entry participant
     */
    private function storeManual(Request $request)
    {
        // PWA Participant Create Request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pwa_participants,email',
            'username' => 'required|string|max:255|unique:pwa_participants,username',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'identity_card' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'job_title' => 'nullable|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'manual_state' => 'nullable|string|max:255',
            'manual_city' => 'nullable|string|max:255',
            'manual_postcode' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id',
            'auto_generate_password' => 'boolean',
            'send_welcome_email' => 'boolean',
            'is_active' => 'boolean',
            'related_participant_id' => 'nullable|exists:participants,id'
        ]);

        $user = Auth::user();

        // Check if user has permission to assign to selected events
        if (!$user->hasRole('Administrator')) {
            $userEvents = Event::where('user_id', $user->id)->pluck('id');
            $invalidEvents = array_diff($request->event_ids, $userEvents->toArray());
            if (!empty($invalidEvents)) {
                return back()->withErrors(['event_ids' => 'You can only assign participants to your own events.']);
            }
        }

        // Generate password if auto-generate is enabled
        $password = null;
        if ($request->boolean('auto_generate_password')) {
            $password = Str::random(12);
        }

        // Combine address fields
        $address = $this->combineAddressFields($request);

        // Create PWA participant
        $participant = PwaParticipant::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'address' => $address,
            'identity_card' => $request->identity_card,
            'passport_no' => $request->passport_no,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'job_title' => $request->job_title,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'notes' => $request->notes,
            'password' => $password ? Hash::make($password) : null,
            'is_active' => $request->boolean('is_active', true),
            'password_changed_at' => $password ? now() : null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'related_participant_id' => $request->related_participant_id ?? null
        ]);

        // Assign to events
        $participant->events()->attach($request->event_ids);

        // Send welcome email if enabled
        if ($request->boolean('send_welcome_email') && $password) {
            $this->sendWelcomeEmail($participant, $password);
        }

        // Also create regular participant record for consistency
        $this->createRegularParticipant($participant, $request->event_ids[0], $request);

        return redirect()->route('pwa.participants')
            ->with('success', 'PWA participant created successfully.');
    }

    /**
     * Combine address fields into a single string
     */
    private function combineAddressFields($request)
    {
        $addressParts = [];
        
        if ($request->address1) $addressParts[] = $request->address1;
        if ($request->address2) $addressParts[] = $request->address2;
        
        // Handle state, city, postcode
        $state = $request->state === 'others' ? $request->manual_state : $request->state;
        $city = $request->state === 'others' ? $request->manual_city : $request->city;
        $postcode = $request->state === 'others' ? $request->manual_postcode : $request->postcode;
        
        if ($city) $addressParts[] = $city;
        if ($state) $addressParts[] = $state;
        if ($postcode) $addressParts[] = $postcode;
        if ($request->country) $addressParts[] = $request->country;
        
        return implode("\n", $addressParts);
    }

    /**
     * Store auto-assign from regular participants
     */
    private function storeAutoAssign(Request $request)
    {
        // PWA Participant Auto-Assign Request
        try {
            // Normalize boolean fields from string to boolean
            foreach (['send_welcome_email', 'is_active', 'force_password_change'] as $field) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    if ($val === '' || $val === null) {
                        $request->request->remove($field);
                    } elseif ($val === 'true' || $val === 1 || $val === '1' || $val === true) {
                        $request->merge([$field => true]);
                    } else {
                        $request->merge([$field => false]);
                    }
                }
            }

            $request->validate([
                'participant_ids' => 'required|string', // JSON string
                'send_welcome_email' => 'sometimes|boolean',
                'is_active' => 'sometimes|boolean',
                'force_password_change' => 'sometimes|boolean'
            ]);

            $participantIds = json_decode($request->participant_ids, true);
            $user = Auth::user();
            $convertedCount = 0;

            // Get regular participants
            $regularParticipants = \App\Models\Participant::whereIn('id', $participantIds)->get();

            foreach ($regularParticipants as $regularParticipant) {
                // Check if PWA participant already exists
                $existingPwaParticipant = PwaParticipant::where('email', $regularParticipant->email)->first();
                if ($existingPwaParticipant) {
                    continue; // Skip if already exists
                }

                // Generate password
                $password = Str::random(12);

                // Generate unique username
                $username = $this->generateUniqueUsername($regularParticipant->name, $regularParticipant->email);

                // Create PWA participant
                $participantId = $regularParticipant->id;
                // Auto-assign: Regular participant ID
                
                $pwaParticipant = PwaParticipant::create([
                    'name' => $regularParticipant->name,
                    'email' => $regularParticipant->email,
                    'username' => $username,
                    'phone' => $regularParticipant->phone,
                    'organization' => $regularParticipant->organization,
                    'address' => $regularParticipant->address ?? '',
                    'identity_card' => $regularParticipant->identity_card ?? '',
                    'passport_no' => $regularParticipant->passport_no ?? '',
                    'gender' => $regularParticipant->gender ?? '',
                    'date_of_birth' => $regularParticipant->date_of_birth ?? '',
                    'job_title' => $regularParticipant->job_title ?? '',
                    'address1' => $regularParticipant->address1 ?? '',
                    'address2' => $regularParticipant->address2 ?? '',
                    'city' => $regularParticipant->city ?? '',
                    'state' => $regularParticipant->state ?? '',
                    'postcode' => $regularParticipant->postcode ?? '',
                    'country' => $regularParticipant->country ?? '',
                    'notes' => $regularParticipant->notes ?? '',
                    'password' => Hash::make($password),
                    'is_active' => $request->boolean('is_active', true),
                    'password_changed_at' => $request->boolean('force_password_change') ? null : now(),
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'related_participant_id' => $participantId
                ]);
                // PWA Participant Created (Auto-Assign)

                // Assign to the same event as regular participant
                if ($regularParticipant->event_id) {
                    $pwaParticipant->events()->attach($regularParticipant->event_id);
                }

                // Send welcome email if enabled
                if ($request->boolean('send_welcome_email')) {
                    $this->sendWelcomeEmail($pwaParticipant, $password);
                }

                $convertedCount++;
            }

            return response()->json([
                'success' => true,
                'converted_count' => $convertedCount,
                'message' => "Successfully converted {$convertedCount} participants to PWA users."
            ]);
        } catch (\Throwable $e) {
            \Log::error('Auto-assign error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store bulk import from file
     */
    private function storeBulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // 5MB max
            'default_event_id' => 'nullable|exists:events,id',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $importedCount = 0;
        $errors = [];

        // Check if user has permission to assign to default event
        if ($request->default_event_id && !$user->hasRole('Administrator')) {
            $userEvents = Event::where('user_id', $user->id)->pluck('id');
            if (!$userEvents->contains($request->default_event_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign participants to your own events.'
                ], 400);
            }
        }

        try {
            // Read file based on type
            $data = $this->readImportFile($file);
            
            foreach ($data as $index => $row) {
                try {
                    // Validate row data
                    $validator = Validator::make($row, [
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|unique:pwa_participants,email',
                        'phone' => 'nullable|string|max:20',
                        'organization' => 'nullable|string|max:255',
                        'address' => 'nullable|string|max:500',
                        'event_id' => 'nullable|exists:events,id',
                        'identity_card' => 'nullable|string|max:255',
                        'passport_no' => 'nullable|string|max:255',
                        'gender' => 'nullable|in:male,female,other',
                        'date_of_birth' => 'nullable|date',
                        'job_title' => 'nullable|string|max:255',
                        'notes' => 'nullable|string|max:1000',
                        'address1' => 'nullable|string|max:255',
                        'address2' => 'nullable|string|max:255',
                        'state' => 'nullable|string|max:255',
                        'city' => 'nullable|string|max:255',
                        'postcode' => 'nullable|string|max:10',
                        'country' => 'nullable|string|max:255',
                        'participant_id' => 'nullable|exists:participants,id'
                    ]);

                    if ($validator->fails()) {
                        $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    // Check event permission
                    $eventId = $row['event_id'] ?? $request->default_event_id;
                    if ($eventId && !$user->hasRole('Administrator')) {
                        $userEvents = Event::where('user_id', $user->id)->pluck('id');
                        if (!$userEvents->contains($eventId)) {
                            $errors[] = "Row " . ($index + 2) . ": You can only assign participants to your own events.";
                            continue;
                        }
                    }

                    // Generate password and username
                    $password = Str::random(12);
                    $username = $this->generateUniqueUsername($row['name'], $row['email']);

                    // Create PWA participant
                    $participant = PwaParticipant::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'username' => $username,
                        'phone' => $row['phone'] ?? null,
                        'organization' => $row['organization'] ?? null,
                        'address' => $row['address'] ?? null,
                        'identity_card' => $row['identity_card'] ?? null,
                        'passport_no' => $row['passport_no'] ?? null,
                        'gender' => $row['gender'] ?? null,
                        'date_of_birth' => $row['date_of_birth'] ?? null,
                        'job_title' => $row['job_title'] ?? null,
                        'notes' => $row['notes'] ?? null,
                        'address1' => $row['address1'] ?? null,
                        'address2' => $row['address2'] ?? null,
                        'state' => $row['state'] ?? null,
                        'city' => $row['city'] ?? null,
                        'postcode' => $row['postcode'] ?? null,
                        'country' => $row['country'] ?? null,
                        'password' => Hash::make($password),
                        'is_active' => $request->boolean('is_active', true),
                        'password_changed_at' => now(),
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'related_participant_id' => $row['participant_id'] ?? null
                    ]);

                    // Assign to event
                    if ($eventId) {
                        $participant->events()->attach($eventId);
                    }

                    // Send welcome email
                    $this->sendWelcomeEmail($participant, $password);

                    // Create regular participant record
                    $this->createRegularParticipant($participant, $eventId, $row);

                    $importedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reading file: ' . $e->getMessage()
            ], 400);
        }

        $message = "Successfully imported {$importedCount} participants.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " and " . (count($errors) - 5) . " more errors.";
            }
        }

        return response()->json([
            'success' => true,
            'imported_count' => $importedCount,
            'message' => $message
        ]);
    }

    /**
     * Read import file and return array of data
     */
    private function readImportFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $data = [];

        if ($extension === 'csv') {
            $handle = fopen($file->getPathname(), 'r');
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        } else {
            // For Excel files, you would need to install a package like PhpSpreadsheet
            // For now, we'll throw an exception
            throw new \Exception('Excel file import not implemented yet. Please use CSV format.');
        }

        return $data;
    }

    /**
     * Send welcome email to participant
     */
    private function sendWelcomeEmail($participant, $password)
    {
        try {
            // You can implement your email sending logic here
            // For now, we'll just log it
            // Welcome email would be sent
            
            // Example email sending (uncomment when you have email configured):
            /*
            Mail::send('emails.pwa-welcome', [
                'participant' => $participant,
                'password' => $password
            ], function($message) use ($participant) {
                $message->to($participant->email)
                        ->subject('Welcome to PWA - Your Login Credentials');
            });
            */
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique username for PWA participant
     */
    private function generateUniqueUsername($name, $email)
    {
        // Create base username from name
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $baseUsername = substr($baseUsername, 0, 10); // Limit to 10 characters
        
        $username = $baseUsername;
        $counter = 1;
        
        // Check if username exists and generate unique one
        while (PwaParticipant::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Create regular participant record for consistency
     */
    private function createRegularParticipant($pwaParticipant, $eventId, $request = null)
    {
        try {
            // Check if regular participant already exists
            $existingParticipant = Participant::where('email', $pwaParticipant->email)->first();
            if (!$existingParticipant) {
                $participantData = [
                    'name' => $pwaParticipant->name,
                    'email' => $pwaParticipant->email,
                    'phone' => $pwaParticipant->phone,
                    'organization' => $pwaParticipant->organization,
                    'event_id' => $eventId,
                    'status' => 'registered',
                    'registration_date' => now()
                ];

                // Add additional fields if available from request (manual entry)
                if ($request && is_object($request)) {
                    if ($request->identity_card) $participantData['identity_card'] = $request->identity_card;
                    if ($request->passport_no) $participantData['passport_no'] = $request->passport_no;
                    if ($request->gender) $participantData['gender'] = $request->gender;
                    if ($request->date_of_birth) $participantData['date_of_birth'] = $request->date_of_birth;
                    if ($request->job_title) $participantData['job_title'] = $request->job_title;
                    if ($request->notes) $participantData['notes'] = $request->notes;
                    
                    // Address fields
                    if ($request->address1) $participantData['address1'] = $request->address1;
                    if ($request->address2) $participantData['address2'] = $request->address2;
                    
                    // Handle state, city, postcode
                    if ($request->state === 'others') {
                        if ($request->manual_state) $participantData['state'] = $request->manual_state;
                        if ($request->manual_city) $participantData['city'] = $request->manual_city;
                        if ($request->manual_postcode) $participantData['postcode'] = $request->manual_postcode;
                    } else {
                        if ($request->state) $participantData['state'] = $request->state;
                        if ($request->city) $participantData['city'] = $request->city;
                        if ($request->postcode) $participantData['postcode'] = $request->postcode;
                    }
                    
                    if ($request->country) $participantData['country'] = $request->country;
                }
                
                // Add additional fields if available from CSV row (array)
                if ($request && is_array($request)) {
                    if (isset($request['identity_card'])) $participantData['identity_card'] = $request['identity_card'];
                    if (isset($request['passport_no'])) $participantData['passport_no'] = $request['passport_no'];
                    if (isset($request['gender'])) $participantData['gender'] = $request['gender'];
                    if (isset($request['date_of_birth'])) $participantData['date_of_birth'] = $request['date_of_birth'];
                    if (isset($request['job_title'])) $participantData['job_title'] = $request['job_title'];
                    if (isset($request['notes'])) $participantData['notes'] = $request['notes'];
                    if (isset($request['address1'])) $participantData['address1'] = $request['address1'];
                    if (isset($request['address2'])) $participantData['address2'] = $request['address2'];
                    if (isset($request['state'])) $participantData['state'] = $request['state'];
                    if (isset($request['city'])) $participantData['city'] = $request['city'];
                    if (isset($request['postcode'])) $participantData['postcode'] = $request['postcode'];
                    if (isset($request['country'])) $participantData['country'] = $request['country'];
                }

                Participant::create($participantData);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create regular participant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified PWA participant
     */
    public function show(PwaParticipant $participant)
    {
        $user = Auth::user();
        // Check if user can view this participant
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $participantEvents = $participant->events->pluck('id');
            if ($participantEvents->intersect($organizerEvents)->isEmpty()) {
                abort(403, 'You can only view participants from your own events.');
            }
        }
        // Load relationships
        $participant->load(['creator', 'updater']);

        // Get all participant records by IC/email (same as API aggregation logic)
        $participantIds = collect();
        if (!empty($participant->identity_card)) {
            $normalizedIc = preg_replace('/\D+/', '', (string) $participant->identity_card);
            $participantIds = $participantIds->merge(\App\Models\Participant::whereRaw("REPLACE(identity_card, '-', '') = ?", [$normalizedIc])->pluck('id'));
        }
        $participantIds = $participantIds->merge(\App\Models\Participant::where('email', $participant->email)->pluck('id'))->unique()->values();

        $participants = \App\Models\Participant::whereIn('id', $participantIds->all())->with('event')->get();

        // Prepare event info with registration/attendance from participants table
        $eventDetails = $participants->map(function($p) {
            $event = $p->event;
            if (!$event) return null;

            // Get attendance records for this participant
            $attendanceRecords = \App\Models\AttendanceRecord::where('participant_id', $p->id)->get();
            $sessions = $attendanceRecords->map(function($record) {
                return [
                    'session' => $record->attendanceSession,
                    'checkin_time' => $record->checkin_time,
                    'checkout_time' => $record->checkout_time,
                    'status' => $record->status,
                ];
            });

            return [
                'event' => $event,
                'event_name' => $event->name,
                'is_registered' => true,
                'registered_at' => $p->created_at,
                'checked_in_at' => $attendanceRecords->first()?->checkin_time,
                'checked_out_at' => $attendanceRecords->first()?->checkout_time,
                'pivot_notes' => $p->notes,
                'attendance_status' => $attendanceRecords->first()?->status,
                'sessions' => $sessions,
            ];
        })->filter();

        // Compute status for display
        $status = $participant->is_active ? 'active' : 'inactive';

        return view('ecertificate.participants.show', [
            'participant' => $participant,
            'eventDetails' => $eventDetails,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for editing the specified PWA participant
     */
    public function edit(PwaParticipant $participant)
    {
        $user = Auth::user();
        
        // Check if user can edit this participant
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $participantEvents = $participant->events->pluck('id');
            
            if ($participantEvents->intersect($organizerEvents)->isEmpty()) {
                abort(403, 'You can only edit participants from your own events.');
            }
        }

        // Get events based on user role
        if ($user->hasRole('Administrator')) {
            $events = Event::all();
        } else {
            $events = Event::where('user_id', $user->id)->get();
        }

        return view('ecertificate.participants.edit', compact('participant', 'events'));
    }

    /**
     * Update the specified PWA participant
     */
    public function update(Request $request, PwaParticipant $participant)
    {
        $user = Auth::user();
        
        // Check if user can edit this participant
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $participantEvents = $participant->events->pluck('id');
            
            if ($participantEvents->intersect($organizerEvents)->isEmpty()) {
                abort(403, 'You can only edit participants from your own events.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pwa_participants,email,' . $participant->id,
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id'
        ]);

        // Verify organizer can only assign participants to their own events
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $requestedEvents = collect($request->event_ids);
            
            if (!$requestedEvents->every(function($eventId) use ($organizerEvents) {
                return $organizerEvents->contains($eventId);
            })) {
                return back()->withErrors(['event_ids' => 'You can only assign participants to your own events.']);
            }
        }

        $participant->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'address' => $request->address,
            'identity_card' => $request->identity_card,
            'passport_no' => $request->passport_no,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'job_title' => $request->job_title,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $user->id
        ]);

        // Sync events
        $participant->events()->sync($request->event_ids);

        return redirect()->route('pwa.participants')->with('success', 'PWA participant updated successfully.');
    }

    /**
     * Remove the specified PWA participant
     */
    public function destroy(PwaParticipant $participant)
    {
        $user = Auth::user();
        
        // Check if user can delete this participant
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $participantEvents = $participant->events->pluck('id');
            
            if ($participantEvents->intersect($organizerEvents)->isEmpty()) {
                abort(403, 'You can only delete participants from your own events.');
            }
        }

        $participant->delete();

        return redirect()->route('pwa.participants')->with('success', 'PWA participant deleted successfully.');
    }

    /**
     * Reset password for PWA participant
     */
    public function resetPassword(PwaParticipant $participant)
    {
        $user = Auth::user();
        
        // Check if user can reset password for this participant
        if (!$user->hasRole('Administrator')) {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $participantEvents = $participant->events->pluck('id');
            
            if ($participantEvents->intersect($organizerEvents)->isEmpty()) {
                abort(403, 'You can only reset passwords for participants from your own events.');
            }
        }

        // Generate new password
        $newPassword = \Illuminate\Support\Str::random(12);
        
        $participant->update([
            'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
            'password_changed_at' => now(),
            'updated_by' => $user->id
        ]);

        // Attempt to send email using DeliveryConfig + PWA template
        $emailSentMsg = '';
        try {
            // If participant has no email, skip sending
            if (!empty($participant->email)) {
                // Load organizer's active email config
                $config = \App\Models\DeliveryConfig::getEmailConfig($user->id);

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

                    // Find password reset template (organizer scope → fallback global → fallback default)
                    $template = \App\Models\PwaEmailTemplate::query()
                        ->where('type', 'password_reset')
                        ->where(function($q) use ($user) {
                            if ($user->hasRole('Administrator')) {
                                $q->where('scope', 'global');
                            } else {
                                $q->where(function($qq) use ($user) {
                                    $qq->where('scope', 'organizer')->where('user_id', $user->id);
                                })->orWhere('scope', 'global');
                            }
                        })
                        ->orderByRaw("CASE WHEN scope='organizer' THEN 0 ELSE 1 END")
                        ->first();

                    $subject = 'Password Reset - E-Certificate Online';
                    $content = '<p><strong>Dear @{{name}},</strong></p><p>Your password has been reset.</p><div class="bg-gray-50 p-3 rounded my-4"><p class="text-sm"><strong>New Password:</strong> @{{password}}</p></div><p>Please login at @{{login_url}} and change your password.</p>';
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
                        'event_name' => '',
                        'organization' => $user->name ?? 'Organizer',
                    ];

                    // Replace variables (follow pattern used in PWA Templates)
                    foreach ($dataVars as $key => $val) {
                        $subject = str_replace('@{{' . $key . '}}', $val, $subject);
                        $content = str_replace('@{{' . $key . '}}', $val, $content);
                    }

                    // Clean + tracking helpers
                    $html = \App\Helpers\EmailHelper::cleanHtml($content);
                    $html = \App\Helpers\EmailHelper::replaceLinksWithTracking($html, $template->id ?? 0, $participant->email);
                    $html = \App\Helpers\EmailHelper::appendOpenTrackingPixel($html, $template->id ?? 0, $participant->email);

                    // Send the email
                    \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($participant, $subject, $fromName, $fromAddress) {
                        $message->to($participant->email)
                                ->subject($subject)
                                ->from($fromAddress, $fromName);
                    });

                    // Log usage if template exists
                    if ($template) {
                        $template->incrementUsage();
                        \App\Models\PwaEmailLog::create([
                            'template_id' => $template->id,
                            'action' => 'sent',
                            'quantity' => 1,
                            'meta' => ['to' => $participant->email, 'context' => 'password_reset']
                        ]);
                    }

                    $emailSentMsg = ' Notification email has been sent to ' . $participant->email . '.';
                } else {
                    $emailSentMsg = ' (No active email configuration found; email was not sent)';
                }
            } else {
                $emailSentMsg = ' (Participant does not have an email address)';
            }
        } catch (\Throwable $e) {
            \Log::error('PWA reset password email failed', ['error' => $e->getMessage()]);
            $emailSentMsg = ' (Email failed to send: ' . $e->getMessage() . ')';
        }

        return redirect()->route('pwa.participants')->with('success', 'Password has been reset successfully.' . $emailSentMsg);
    }
} 