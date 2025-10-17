<?php

namespace App\Events;

use App\Models\HelpdeskMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageSent
{
    use Dispatchable, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpdeskMessage $message)
    {
        $this->message = $message->load(['user', 'ticket']);
    }

    // No broadcasting - FCM will be triggered from controller/service instead
}
