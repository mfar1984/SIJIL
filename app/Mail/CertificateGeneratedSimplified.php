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
use Illuminate\Support\Facades\URL;

class CertificateGeneratedSimplified extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $participant;
    public $certificate;
    public $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, Participant $participant, Certificate $certificate)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->certificate = $certificate;
        
        // Generate signed URL valid for 30 days
        $this->downloadUrl = URL::temporarySignedRoute(
            'certificates.download.simplified',
            now()->addDays(30),
            ['certificate' => $certificate->id]
        );
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
            view: 'emails.certificate-generated-simplified',
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
