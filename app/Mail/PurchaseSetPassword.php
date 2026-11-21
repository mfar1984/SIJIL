<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent after a paid purchase created a new Organizer account. Carries a
 * single-use set-password link (password broker "purchases", valid 7 days).
 * Sent synchronously through PaymentMailer; never queued.
 */
class PurchaseSetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public function __construct(
        public User $user,
        #[\SensitiveParameter] string $token,
        public string $packageName,
        public ?string $expiresOn,
    ) {
        $this->url = route('account.set-password', ['token' => $token, 'email' => $user->email]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Set your e-Certificate password');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.purchase-set-password');
    }
}
