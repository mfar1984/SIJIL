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
    public function index()
    {
        // For Administrator, show all participants
        // For Organizer, show only participants of their events
        if (auth()->user()->hasRole('Administrator')) {
            $participants = Participant::with('event')->paginate(10);
        } else {
            // Get events created by the current user
            $userEvents = Event::where('user_id', auth()->id())->pluck('id');
            // Get participants for those events
            $participants = Participant::whereIn('event_id', $userEvents)->with('event')->paginate(10);
        }

        return view('participants', [
            'participants' => $participants
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
            'organization' => 'nullable|string|max:255',
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

        // Create a new participant
        $participant = new Participant();
        $participant->name = $request->name;
        $participant->email = $request->email;
        $participant->phone = $request->phone;
        $participant->organization = $request->organization;
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

        return view('participants.show', [
            'participant' => $participant
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
            'organization' => 'nullable|string|max:255',
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

        // Update the participant
        $participant->name = $request->name;
        $participant->email = $request->email;
        $participant->phone = $request->phone;
        $participant->organization = $request->organization;
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