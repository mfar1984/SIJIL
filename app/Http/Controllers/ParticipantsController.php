<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class ParticipantsController extends Controller
{
    /**
     * Display the participants management page with data from database.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = Participant::with('event');

        // For non-Administrator users, filter by their events
        if (!auth()->user()->hasRole('Administrator')) {
            $userEvents = Event::where('user_id', auth()->id())->pluck('id');
            $query->whereIn('event_id', $userEvents);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('identity_card', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('passport_no', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('organization', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event_id', $request->event);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $participants = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get events for filter dropdown
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('name')->get();
        } else {
            $events = Event::where('user_id', auth()->id())->orderBy('name')->get();
        }

        return view('participants', [
            'participants' => $participants,
            'events' => $events
        ]);
    }

    /**
     * Show the form for creating a new participant.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get available events based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::all();
        } else {
            $events = Event::where('user_id', auth()->id())->get();
        }

        return view('participants.create', [
            'events' => $events
        ]);
    }

    /**
     * Store a newly created participant in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'identity_card' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'race' => 'nullable|string|max:100',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'manual_state' => 'nullable|string|max:100',
            'manual_city' => 'nullable|string|max:100',
            'manual_postcode' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'organization' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        // Check if the user has permission to add participants to this event
        $event = Event::findOrFail($request->event_id);
        if (!auth()->user()->hasRole('Administrator') && $event->user_id != auth()->id()) {
            return redirect()->route('participants')
                ->with('error', 'You do not have permission to add participants to this event.');
        }

        // Process address fields
        $state = $request->state;
        $city = $request->city;
        $postcode = $request->postcode;
        
        // If "others" is selected for state, use the manual input fields
        if ($request->state == 'others') {
            $state = $request->manual_state;
            $city = $request->manual_city;
            $postcode = $request->manual_postcode;
        }

        // Create a new participant
        $participant = new Participant();
        $participant->name = $request->name;
        $participant->email = $request->email;
        $participant->phone = $request->phone;
        
        // Handle identity card and passport fields
        $participant->identity_card = $request->identity_card;
        $participant->passport_no = $request->passport_no;
        
        $participant->date_of_birth = $request->date_of_birth;
        $participant->race = $request->race;
        
        // Update address fields individually
        $participant->address1 = $request->address1;
        $participant->address2 = $request->address2;
        $participant->city = $city;
        $participant->state = $state;
        $participant->postcode = $postcode;
        $participant->country = $request->country ?? 'Malaysia';
        
        $participant->gender = $request->gender;
        $participant->organization = $request->organization;
        $participant->job_title = $request->job_title;
        $participant->status = $request->status;
        $participant->event_id = $request->event_id;
        $participant->registration_date = now();
        $participant->notes = $request->notes;

        // Save the participant
        $participant->save();

        return redirect()->route('participants')
            ->with('success', 'Participant added successfully!');
    }

    /**
     * Display the specified participant.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $participant = Participant::with('event')->findOrFail($id);

        // Check if the user has permission to view this participant
        if (!$this->canAccessParticipant($participant)) {
            return redirect()->route('participants')
                ->with('error', 'You do not have permission to view this participant.');
        }

        // Get all attendance records for this participant, with attendance, session, and certificate
        $attendanceRecords = \App\Models\AttendanceRecord::with(['attendance.event', 'attendanceSession'])
            ->where('participant_id', $participant->id)
            ->get();

        // Get all certificates for this participant
        $certificates = \App\Models\Certificate::where('participant_id', $participant->id)->get();

        return view('participants.show', [
            'participant' => $participant,
            'attendanceRecords' => $attendanceRecords,
            'certificates' => $certificates,
        ]);
    }

    /**
     * Show the form for editing the specified participant.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $participant = Participant::findOrFail($id);

        // Check if the user has permission to edit this participant
        if (!$this->canAccessParticipant($participant)) {
            return redirect()->route('participants')
                ->with('error', 'You do not have permission to edit this participant.');
        }

        // Get available events based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::all();
        } else {
            $events = Event::where('user_id', auth()->id())->get();
        }

        return view('participants.edit', [
            'participant' => $participant,
            'events' => $events
        ]);
    }

    /**
     * Update the specified participant in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'identity_card' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'race' => 'nullable|string|max:100',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'manual_state' => 'nullable|string|max:100',
            'manual_city' => 'nullable|string|max:100',
            'manual_postcode' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'organization' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $participant = Participant::findOrFail($id);

        // Check if the user has permission to update this participant
        if (!$this->canAccessParticipant($participant)) {
            return redirect()->route('participants')
                ->with('error', 'You do not have permission to update this participant.');
        }

        // Check if the user has permission to move participants to the target event
        if ($participant->event_id != $request->event_id) {
            $targetEvent = Event::findOrFail($request->event_id);
            if (!auth()->user()->hasRole('Administrator') && $targetEvent->user_id != auth()->id()) {
                return redirect()->route('participants')
                    ->with('error', 'You do not have permission to move participants to this event.');
            }
        }

        // Process address fields
        $state = $request->state;
        $city = $request->city;
        $postcode = $request->postcode;
        
        // If "others" is selected for state, use the manual input fields
        if ($request->state == 'others') {
            $state = $request->manual_state;
            $city = $request->manual_city;
            $postcode = $request->manual_postcode;
        }

        // Update the participant
        $participant->name = $request->name;
        $participant->email = $request->email;
        $participant->phone = $request->phone;
        
        // Handle identity card and passport fields
        $participant->identity_card = $request->identity_card;
        $participant->passport_no = $request->passport_no;
        
        $participant->date_of_birth = $request->date_of_birth;
        $participant->race = $request->race;
        
        // Update address fields individually
        $participant->address1 = $request->address1;
        $participant->address2 = $request->address2;
        $participant->city = $city;
        $participant->state = $state;
        $participant->postcode = $postcode;
        $participant->country = $request->country ?? 'Malaysia';
        
        $participant->gender = $request->gender;
        $participant->organization = $request->organization;
        $participant->job_title = $request->job_title;
        $participant->status = $request->status;
        $participant->event_id = $request->event_id;
        $participant->notes = $request->notes;

        // Save the participant
        $participant->save();

        return redirect()->route('participants')
            ->with('success', 'Participant updated successfully!');
    }

    /**
     * Remove the specified participant from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $participant = Participant::findOrFail($id);

        // Check if the user has permission to delete this participant
        if (!$this->canAccessParticipant($participant)) {
            return redirect()->route('participants')
                ->with('error', 'You do not have permission to delete this participant.');
        }

        // Delete the participant
        $participant->delete();

        return redirect()->route('participants')
            ->with('success', 'Participant deleted successfully!');
    }

    /**
     * Check if the current user has permission to access a participant.
     *
     * @param  \App\Models\Participant  $participant
     * @return bool
     */
    private function canAccessParticipant(Participant $participant)
    {
        // Administrator can access all participants
        if (auth()->user()->hasRole('Administrator')) {
            return true;
        }

        // Organizer can only access participants from their events
        $event = Event::find($participant->event_id);
        return $event && $event->user_id == auth()->id();
    }
} 