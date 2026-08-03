<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an administrator creates a backend account, if "Email new users a
 * welcome message" is on. Carries no password: see the view for why.
 */
class UserWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $roleName = null,
        public ?string $createdBy = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your account on ' . (\App\Models\GlobalConfig::getConfig()->org_name ?: config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-welcome',
        );
    }
}
