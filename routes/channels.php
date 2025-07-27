<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Helpdesk admin channel - only administrators can listen
Broadcast::channel('helpdesk.admin', function ($user) {
    return $user->hasRole('Administrator');
});

// Helpdesk ticket channel - only administrators and the ticket owner can listen
Broadcast::channel('helpdesk.ticket.{ticketId}', function ($user, $ticketId) {
    if ($user->hasRole('Administrator')) {
        return true;
    }
    
    $ticket = \App\Models\HelpdeskTicket::find($ticketId);
    return $ticket && $ticket->user_id === $user->id;
});

// Helpdesk user channel - only administrators and the specific user can listen
Broadcast::channel('helpdesk.user.{userId}', function ($user, $userId) {
    if ($user->hasRole('Administrator')) {
        return true;
    }
    
    return (int) $user->id === (int) $userId;
}); 