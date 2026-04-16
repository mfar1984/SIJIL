<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateGeneratedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $participant;
    public $certificate;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, Participant $participant, Certificate $certificate)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->certificate = $certificate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Certificate is Ready - ' . $this->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-generated-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
