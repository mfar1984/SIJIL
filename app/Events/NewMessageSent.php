<?php

namespace App\Events;

use App\Models\HelpdeskMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpdeskMessage $message)
    {
        $this->message = $message->load(['user', 'ticket']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('helpdesk.ticket.' . $this->message->ticket_id),
        ];
        
        // If message is not internal, also broadcast to admin channel
        if (!$this->message->is_internal) {
            $channels[] = new PrivateChannel('helpdesk.admin');
        }
        
        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $attachments = [];
        if ($this->message->attachments) {
            foreach ($this->message->attachments as $index => $attachment) {
                $attachments[] = [
                    'index' => $index,
                    'name' => $attachment['name'],
                    'size' => $attachment['size'],
                    'mime' => $attachment['mime'],
                ];
            }
        }
        
        return [
            'id' => $this->message->id,
            'ticket_id' => $this->message->ticket_id,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'message' => $this->message->message,
            'is_internal' => $this->message->is_internal,
            'attachments' => $attachments,
            'created_at' => $this->message->created_at->format('Y-m-d H:i:s'),
            'ticket' => [
                'id' => $this->message->ticket->id,
                'ticket_id' => $this->message->ticket->ticket_id,
                'subject' => $this->message->ticket->subject,
            ],
        ];
    }
}
