<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent after a paid purchase extended an existing Organizer's package.
 */
class PackageRenewed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $packageName,
        public string $expiresOn,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your e-Certificate package has been renewed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.package-renewed');
    }
}
