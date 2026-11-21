<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent by packages:remind 7 days and 1 day before an Organizer's package expires.
 */
class PackageExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public string $pricingUrl;

    public function __construct(
        public User $user,
        public int $daysLeft,
        public string $expiresOn,
    ) {
        $this->pricingUrl = (string) config('payments.pricing_url');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->daysLeft <= 1
            ? 'Your e-Certificate package expires tomorrow'
            : 'Your e-Certificate package expires in '.$this->daysLeft.' days');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.package-expiry-reminder');
    }
}
