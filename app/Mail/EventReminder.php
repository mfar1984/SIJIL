<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public Participant $participant,
        public int $hoursAhead
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: ' . $this->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-reminder',
        );
    }
}
