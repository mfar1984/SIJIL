<?php

namespace App\Events;

use App\Models\HelpdeskTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpdeskTicket $ticket)
    {
        $this->ticket = $ticket->load(['user', 'assignedUser']);
    }

    // No broadcasting - FCM will be triggered from controller/service instead
}
