<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantSearchController extends Controller
{
    /**
     * Search regular participants for PWA auto-assign functionality
     */
    public function search(Request $request)
    {
        $user = Auth::user();

        // The route now guarantees a signed-in user, but the guard stays: this
        // controller was previously reachable without one, and the tenant filter
        // below was written as `if ($user && ...)` so it simply did not run.
        // Returning nothing is the safe reading of "no user".
        if (! $user) {
            abort(401);
        }

        $query = Participant::with('event:id,name');

        // Multi-tenant data filtering based on user role
        if (! $user->hasRole('Administrator')) {
            // Organizer can only see participants from their own events
            $organizerEvents = Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $organizerEvents);
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

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Exclude participants that already have PWA accounts
        $query->whereNotExists(function($q) {
            $q->select(\DB::raw(1))
              ->from('pwa_participants')
              ->whereRaw('pwa_participants.email = participants.email');
        });

        // Only the columns the picker renders. This used to return whole Participant
        // models, so every response also carried IC, passport, address, date of
        // birth, gender and race - none of which the interface shows.
        $participants = $query->select(['id', 'name', 'email', 'organization', 'event_id'])
            ->orderBy('name')
            ->limit(50) // Limit to 50 results for performance
            ->get()
            ->map(fn ($participant) => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'organization' => $participant->organization,
                'event' => $participant->event ? ['name' => $participant->event->name] : null,
            ]);

        return response()->json([
            'success' => true,
            'participants' => $participants,
            'total' => $participants->count()
        ]);
    }
} 