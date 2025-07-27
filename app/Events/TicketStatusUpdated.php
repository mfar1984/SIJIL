<?php

namespace App\Events;

use App\Models\HelpdeskTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpdeskTicket $ticket)
    {
        $this->ticket = $ticket->load(['user', 'assignedUser']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('helpdesk.ticket.' . $this->ticket->id),
            new PrivateChannel('helpdesk.user.' . $this->ticket->user_id),
            new PrivateChannel('helpdesk.admin'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'ticket_id' => $this->ticket->ticket_id,
            'subject' => $this->ticket->subject,
            'status' => $this->ticket->status,
            'assigned_to' => $this->ticket->assigned_to,
            'assigned_user' => $this->ticket->assignedUser ? [
                'id' => $this->ticket->assignedUser->id,
                'name' => $this->ticket->assignedUser->name,
            ] : null,
            'updated_at' => $this->ticket->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
