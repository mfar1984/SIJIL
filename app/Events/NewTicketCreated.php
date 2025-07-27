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

class NewTicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpdeskTicket $ticket)
    {
        $this->ticket = $ticket->load(['user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
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
        \Log::info('Broadcasting NewTicketCreated with data: ' . json_encode($this->ticket));
        
        return [
            'id' => $this->ticket->id,
            'ticket_id' => $this->ticket->ticket_id,
            'subject' => $this->ticket->subject,
            'priority' => $this->ticket->priority,
            'category' => $this->ticket->category,
            'status' => $this->ticket->status,
            'created_at' => $this->ticket->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->ticket->user->id,
                'name' => $this->ticket->user->name,
            ],
        ];
    }
}
