<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Show full event details for PWA drawer
     */
    public function show(Request $request, $eventId)
    {
        // Ensure authenticated via sanctum (route group should enforce)
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $data = [
            'id' => $event->id,
            'title' => $event->name,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'time' => ($event->start_time ? substr($event->start_time, 0, 5) : '') . ($event->end_time ? ' - ' . substr($event->end_time, 0, 5) : ''),
            'location' => $event->location,
            'address' => $event->address,
            'organizer' => $event->organizer,
            'contact_organizer' => $event->organizer, // alias for clarity
            'contact_person' => $event->contact_person,
            'contact_phone' => $event->contact_phone,
            'contact_email' => $event->contact_email,
            'terms' => $event->condition,
            'poster' => $event->poster,
            'poster_url' => $event->poster ? url('storage/' . ltrim($event->poster, '/')) : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}


