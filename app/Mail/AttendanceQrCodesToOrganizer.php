<?php

namespace App\Mail;

use App\Models\Event;
use App\Support\AttendanceSummary;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

/**
 * The check-in QR codes, sent to the organizer rather than the participant.
 *
 * Participants must not receive these: anyone holding the image could mark
 * themselves present without attending. The organizer displays them at the venue.
 *
 * How many are attached follows the attendance mode:
 *   - Scan once      one code for the whole event, even a five-day one
 *   - Scan every day one code per day
 *   - Let me choose  one per session window the organizer defined
 */
class AttendanceQrCodesToOrganizer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public AttendanceSummary $attendance,
    ) {
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $count = $this->attendance->qrCount();

        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Attendance QR code' . ($count === 1 ? '' : 's') . ' - ' . $this->event->name,
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.attendance-qr-codes',
            with: [
                'event' => $this->event,
                'attendance' => $this->attendance,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attendance->days as $index => $day) {
            $dayLabel = $day['date']->format('Y-m-d');

            if ($day['checkin_code']) {
                $attachments[] = $this->qrAttachment(
                    $day['checkin_code'],
                    $this->filename('checkin', $dayLabel, $index)
                );
            }

            if ($day['checkout_code']) {
                $attachments[] = $this->qrAttachment(
                    $day['checkout_code'],
                    $this->filename('checkout', $dayLabel, $index)
                );
            }
        }

        return $attachments;
    }

    /**
     * Render a session code as an SVG in memory. Attendance QR codes are not
     * stored on disk anywhere in this system, they are always generated on
     * demand, so the mailable does the same.
     */
    protected function qrAttachment(string $code, string $filename): Attachment
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(500), new SvgImageBackEnd()));

        return Attachment::fromData(fn () => $writer->writeString($code), $filename)
            ->withMime('image/svg+xml');
    }

    /**
     * "Scan once" produces a single code, so naming it after a date would imply
     * it only works that day.
     */
    protected function filename(string $kind, string $dayLabel, int $index): string
    {
        $slug = \Illuminate\Support\Str::slug($this->event->name) ?: 'event';

        if ($this->attendance->type === 'single') {
            return "{$slug}-{$kind}-qr.svg";
        }

        return "{$slug}-day" . ($index + 1) . "-{$dayLabel}-{$kind}-qr.svg";
    }
}
