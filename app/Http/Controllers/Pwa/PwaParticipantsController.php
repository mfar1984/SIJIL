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
use Illuminate\Support\Facades\DB;

class PwaParticipantsController extends Controller
{
    /**
     * Display a listing of PWA participants with role-based filtering
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Who this account may see. Administrators see everything; an organizer
        // sees accounts they created, accounts attached to one of their events,
        // and accounts whose email matches a participant on one of their events.
        //
        // That last rule is how the same person shows up for two organizers when
        // they registered for an event from each: the account is one row, but it
        // is reachable from both events.
        //
        // This used to be a hand-rolled copy of the rule that also required
        // registration_type = 'verified'. Almost every real participant is
        // 'simplified', so organizers saw an empty list. It also compared emails
        // case-sensitively and counted participants that were in the Recycle Bin.
        $query = \App\Support\PwaLink::accountsFor($user)->with(['events', 'certificates']);

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

        // Status filtering. Active and inactive exclude banned accounts, because a
        // ban is not a shade of active and listing it as one hides it.
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->notBanned()->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->notBanned()->where('is_active', false);
            } elseif ($request->status === 'banned') {
                $query->banned();
            }
        }

        // Counts for the summary cards, taken before paginate() applies its
        // own limit and offset to the builder.
        $filteredTotal = (clone $query)->count();
        $filteredActive = (clone $query)->notBanned()->where('is_active', true)->count();
        $filteredNeverSignedIn = (clone $query)->notBanned()->whereNull('password_changed_at')->count();
        $filteredBanned = (clone $query)->banned()->count();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $participants = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Enrich the listing with phone/organization from the matching regular
        // participant. Fetched in one query keyed by email instead of one query
        // per row, which used to add up to 15 queries per page load.
        $emailsNeedingFallback = $participants->getCollection()
            ->filter(fn($pp) => empty($pp->phone) || empty($pp->organization))
            ->pluck('email')
            ->filter()
            ->unique();

        if ($emailsNeedingFallback->isNotEmpty()) {
            // Matched case-insensitively and keyed the same way, because a
            // participant row can hold a differently cased version of the address.
            $lowered = $emailsNeedingFallback->map(fn ($e) => strtolower(trim($e)))->all();

            $fallbackQuery = Participant::whereIn(DB::raw('LOWER(email)'), $lowered);

            if (!$user->hasRole('Administrator')) {
                $fallbackQuery->whereIn('event_id', Event::where('user_id', $user->id)->pluck('id'));
            }

            $fallbacks = $fallbackQuery->latest()->get()->keyBy(fn ($p) => strtolower(trim($p->email)));

            $participants->getCollection()->transform(function($pp) use ($fallbacks) {
                $regular = $fallbacks->get(strtolower(trim((string) $pp->email)));

                if ($regular) {
                    if (empty($pp->phone) && !empty($regular->phone)) {
                        $pp->setAttribute('phone', $regular->phone);
                    }
                    if (empty($pp->organization) && !empty($regular->organization)) {
                        $pp->setAttribute('organization', $regular->organization);
                    }
                }

                return $pp;
            });
        }

        // Get counts for display
        if ($user->hasRole('Administrator')) {
            $totalParticipants = PwaParticipant::count();
            $totalEvents = Event::count();
        } else {
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $totalParticipants = PwaParticipant::where(function($q) use ($user, $organizerEvents) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('events', function($qq) use ($organizerEvents) {
                          $qq->whereIn('events.id', $organizerEvents);
                      })
                      ->orWhereExists(function($sub) use ($user) {
                          $sub->select(DB::raw(1))
                              ->from('participants as rp')
                              ->join('events as ev', 'rp.event_id', '=', 'ev.id')
                              ->whereColumn('rp.email', 'pwa_participants.email')
                              ->where('ev.user_id', $user->id);
                      });
                })
                ->distinct('pwa_participants.id')
                ->count('pwa_participants.id');
            $totalEvents = Event::where('user_id', $user->id)->count();
        }

        // Summary for the cards. "Never signed in" counts accounts that still
        // hold the generated password, which is the clearest sign that the
        // person was never told their credentials.
        $stats = [
            'total' => $filteredTotal,
            'active' => $filteredActive,
            'never_signed_in' => $filteredNeverSignedIn,
            'banned' => $filteredBanned,
        ];

        return view('ecertificate.participants', compact(
            'participants',
            'totalParticipants',
            'totalEvents',
            'stats'
        ));
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
        // pwa_participants.email has a database-level unique index, so an
        // address held by a record in the Recycle Bin cannot be reused yet.
        if ($request->filled('email')) {
            $trashed = PwaParticipant::onlyTrashed()->where('email', $request->email)->first();

            if ($trashed) {
                return back()->withInput()->withErrors([
                    'email' => "This email belongs to \"{$trashed->name}\", a PWA participant sitting in the Recycle Bin. Restore that record, or delete it permanently from Settings â†’ Global Config â†’ Recycle Bin to free up the email.",
                ]);
            }
        }

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
            'race' => 'nullable|string|max:50',
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
            $password = \App\Support\PwaPassword::generate($user ?? Auth::user());
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
            'race' => $request->race,
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
        $emailNote = '';
        if ($request->boolean('send_welcome_email') && $password) {
            $result = $this->sendWelcomeEmail($participant, $password);
            $emailNote = ' ' . $result['message'];
        }

        // Also create regular participant record for consistency
        $this->createRegularParticipant($participant, $request->event_ids[0], $request);

        return redirect()->route('pwa.participants')
            ->with('success', 'PWA participant created successfully.' . $emailNote);
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
            $emailedCount = 0;
            $emailFailures = [];

            // Get regular participants
            $regularParticipants = \App\Models\Participant::whereIn('id', $participantIds)->get();

            foreach ($regularParticipants as $regularParticipant) {
                // Check if PWA participant already exists
                $existingPwaParticipant = PwaParticipant::where('email', $regularParticipant->email)->first();
                if ($existingPwaParticipant) {
                    continue; // Skip if already exists
                }

                // Generate password
                $password = \App\Support\PwaPassword::generate($user ?? Auth::user());

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
                    $result = $this->sendWelcomeEmail(
                        $pwaParticipant,
                        $password,
                        $regularParticipant->event->name ?? ''
                    );

                    if ($result['sent']) {
                        $emailedCount++;
                    } else {
                        $emailFailures[] = $pwaParticipant->email . ': ' . $result['message'];
                    }
                }

                $convertedCount++;
            }

            $message = "Successfully converted {$convertedCount} participants to PWA users.";

            if ($request->boolean('send_welcome_email')) {
                $message .= " {$emailedCount} welcome email(s) sent.";

                if ($emailFailures) {
                    $message .= ' ' . count($emailFailures) . ' could not be emailed.';
                }
            }

            return response()->json([
                'success' => true,
                'converted_count' => $convertedCount,
                'emailed_count' => $emailedCount,
                'email_failures' => $emailFailures,
                'message' => $message,
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
        $emailedCount = 0;
        $sendWelcomeEmail = $request->boolean(
            'send_welcome_email',
            (bool) \App\Models\PwaSetting::valueFor('send_welcome_email', $user)
        );
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
                        'race' => 'nullable|string|max:50',
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
                    $password = \App\Support\PwaPassword::generate($user ?? Auth::user());
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
                        'race' => $row['race'] ?? null,
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

                    // Send welcome email. The import form has no checkbox, so
                    // fall back to the PWA > Settings > Emails preference.
                    if ($sendWelcomeEmail) {
                        $result = $this->sendWelcomeEmail($participant, $password);

                        if ($result['sent']) {
                            $emailedCount++;
                        }
                    }

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
        if ($sendWelcomeEmail) {
            $message .= " {$emailedCount} welcome email(s) sent.";
        }
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " and " . (count($errors) - 5) . " more errors.";
            }
        }

        return response()->json([
            'success' => true,
            'imported_count' => $importedCount,
            'emailed_count' => $emailedCount,
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
     * Email the participant their PWA login credentials.
     *
     * This used to be an empty stub, which is why accounts were created with a
     * generated password that nobody ever received.
     *
     * @return array{sent: bool, message: string}
     */
    private function sendWelcomeEmail($participant, $password, $eventName = '')
    {
        return \App\Support\PwaMailer::send(
            type: 'welcome',
            participant: $participant,
            vars: [
                'password' => $password,
                'username' => $participant->username ?? $participant->email,
                'event_name' => $eventName,
            ],
            sender: Auth::user(),
            fallback: [
                'subject' => 'Welcome to E-Certificate - Your Login Details',
                'content' => '<p><strong>Dear @{{name}},</strong></p>'
                    . '<p>An account has been created for you on the E-Certificate app. '
                    . 'You can use it to view your events, check in with a QR code and download your certificates.</p>'
                    . '<div style="background-color:#f9fafb;padding:12px;border-radius:4px;margin:16px 0">'
                    . '<p style="font-size:14px;margin:0 0 6px"><strong>Email:</strong> @{{email}}</p>'
                    . '<p style="font-size:14px;margin:0"><strong>Password:</strong> @{{password}}</p>'
                    . '</div>'
                    . '<p>Sign in at @{{login_url}} and change your password after your first sign-in.</p>'
                    . '<p style="margin-top:16px;font-size:12px;color:#6b7280">'
                    . 'Need help? Contact us at @{{support_email}}</p>',
            ]
        );
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
    /**
     * Stop here unless this account is one the current user may act on.
     *
     * There used to be a separately written check in show, edit, update, destroy
     * and resetPassword, each with slightly different rules from the listing.
     * They are all answered by the same query now, so what you can open always
     * matches what you can see.
     */
    private function authoriseAccess(PwaParticipant $participant, string $verb): void
    {
        if (!\App\Support\PwaLink::canAccess(Auth::user(), $participant)) {
            abort(403, "You can only {$verb} participants from your own events.");
        }
    }

    public function show(PwaParticipant $participant)
    {
        $this->authoriseAccess($participant, 'view');

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

        // Enrich PWA profile details for view using latest data from regular participants when empty
        if ($participants->isNotEmpty()) {
            $source = $participants->first();
            $fallbackFields = [
                'phone', 'identity_card', 'passport_no', 'gender', 'race', 'date_of_birth', 'job_title',
                'organization', 'address', 'address1', 'address2', 'city', 'state', 'postcode', 'country'
            ];
            foreach ($fallbackFields as $field) {
                if (empty($participant->{$field}) && !empty($source->{$field})) {
                    $participant->setAttribute($field, $source->{$field});
                }
            }
            // If address still empty, compose from granular fields
            if (empty($participant->address)) {
                $addrParts = array_filter([
                    $participant->address1,
                    $participant->address2,
                    $participant->city,
                    $participant->state,
                    $participant->postcode,
                    $participant->country,
                ]);
                if (!empty($addrParts)) {
                    $participant->setAttribute('address', implode("\n", $addrParts));
                }
            }
        }

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
        $this->authoriseAccess($participant, 'edit');

        $user = Auth::user();

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
        $this->authoriseAccess($participant, 'edit');

        $user = Auth::user();

        // The demographic fields were being saved without ever being validated,
        // so a hand-crafted request could store any string in gender.
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pwa_participants,email,' . $participant->id,
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'identity_card' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'race' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'job_title' => 'nullable|string|max:255',
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
            'race' => $request->race,
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
        $this->authoriseAccess($participant, 'delete');

        $participant->delete();

        return redirect()->route('pwa.participants')->with('success', 'PWA participant deleted successfully.');
    }

    /**
     * Ban a participant.
     *
     * A ban does two things: the account can no longer sign in, and the same
     * person cannot register again through a public link using the same email or
     * IC. Deleting the account would not achieve the second part, because nothing
     * would be left to recognise them by.
     */
    public function ban(Request $request, PwaParticipant $participant)
    {
        $this->authoriseAccess($participant, 'ban');

        $request->validate([
            'ban_reason' => 'nullable|string|max:500',
        ]);

        if ($participant->isBanned()) {
            return back()->with('error', 'This participant is already banned.');
        }

        // status is an enum of active/inactive only, so the ban lives in
        // banned_at. is_active is cleared as well so any older code that only
        // checks that flag also stops letting them in.
        $participant->forceFill([
            'banned_at' => now(),
            'banned_by' => Auth::id(),
            'ban_reason' => $request->input('ban_reason'),
            'is_active' => false,
            'updated_by' => Auth::id(),
        ])->save();

        // Existing API tokens would otherwise keep working until they expire.
        $participant->tokens()->delete();

        return redirect()->route('pwa.participants')
            ->with('success', $participant->name . ' has been banned. They can no longer sign in or register again with this email or IC.');
    }

    /**
     * Lift a ban.
     */
    public function unban(PwaParticipant $participant)
    {
        $this->authoriseAccess($participant, 'ban');

        if (!$participant->isBanned()) {
            return back()->with('error', 'This participant is not banned.');
        }

        // Lifting a ban re-enables the account. If it was deliberately inactive
        // before the ban, that has to be set again from the edit form: nothing
        // recorded what it used to be, and guessing would be worse.
        $participant->forceFill([
            'banned_at' => null,
            'banned_by' => null,
            'ban_reason' => null,
            'is_active' => true,
            'updated_by' => Auth::id(),
        ])->save();

        return redirect()->route('pwa.participants')
            ->with('success', 'The ban on ' . $participant->name . ' has been lifted.');
    }

    /**
     * Reset password for PWA participant
     */
    public function resetPassword(PwaParticipant $participant)
    {
        $this->authoriseAccess($participant, 'reset passwords for');

        $user = Auth::user();

        // Resetting a banned account would email a password that cannot be used.
        if ($participant->isBanned()) {
            return back()->with('error', 'This participant is banned, so they cannot sign in. Lift the ban first.');
        }

        if ($wait = $this->resetCooldownRemaining($user)) {
            return back()->with(
                'error',
                "Please wait {$wait} more second" . ($wait === 1 ? '' : 's') . ' before resetting another password.'
            );
        }

        // Generate new password using the configured length and complexity
        $newPassword = \App\Support\PwaPassword::generate($user);
        
        $participant->update([
            'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
            'password_changed_at' => now(),
            'updated_by' => $user->id
        ]);

        // Email the new password. PwaMailer owns the provider lookup, template
        // resolution and logging, and applies the Administrator fallback when this
        // account has no delivery configuration of its own.
        $mail = \App\Support\PwaMailer::send(
            type: 'password_reset',
            participant: $participant,
            vars: ['password' => $newPassword],
            sender: $user,
            fallback: [
                'subject' => 'Password Reset - E-Certificate Online',
                'content' => '<p><strong>Dear @{{name}},</strong></p>'
                    . '<p>Your password has been reset.</p>'
                    . '<div style="background-color:#f9fafb;padding:12px;border-radius:4px;margin:16px 0">'
                    . '<p style="font-size:14px;margin:0"><strong>New Password:</strong> @{{password}}</p>'
                    . '</div>'
                    . '<p>Please sign in at @{{login_url}} and change your password.</p>',
            ]
        );

        $emailSentMsg = ' ' . $mail['message'];

        $this->markResetPerformed($user);

        return redirect()->route('pwa.participants')->with('success', 'Password has been reset successfully.' . $emailSentMsg);
    }

    /**
     * Seconds this user still has to wait before resetting another password.
     *
     * Administrators are never limited: they are the ones who sort it out when an
     * organizer runs into the limit, and when a bulk reset genuinely is needed.
     *
     * The window is held in the cache rather than a table. It is a throttle, not a
     * record: if the cache is cleared the worst outcome is one extra reset.
     *
     * @return int  0 when the user may go ahead
     */
    private function resetCooldownRemaining($user): int
    {
        if (!$user || $user->hasRole('Administrator')) {
            return 0;
        }

        $cooldown = (int) (\App\Models\GlobalConfig::getConfig()->pwa_reset_cooldown_seconds ?? 60);

        if ($cooldown <= 0) {
            return 0;
        }

        $last = \Illuminate\Support\Facades\Cache::get($this->resetCooldownKey($user));

        if (!$last) {
            return 0;
        }

        $elapsed = now()->diffInSeconds(\Illuminate\Support\Carbon::parse($last), true);

        return (int) max(0, $cooldown - $elapsed);
    }

    /**
     * Start the cooldown window for this user.
     */
    private function markResetPerformed($user): void
    {
        if (!$user || $user->hasRole('Administrator')) {
            return;
        }

        $cooldown = (int) (\App\Models\GlobalConfig::getConfig()->pwa_reset_cooldown_seconds ?? 60);

        if ($cooldown <= 0) {
            return;
        }

        \Illuminate\Support\Facades\Cache::put(
            $this->resetCooldownKey($user),
            now()->toIso8601String(),
            now()->addSeconds($cooldown)
        );
    }

    private function resetCooldownKey($user): string
    {
        return 'pwa-reset-cooldown:' . $user->id;
    }
} 