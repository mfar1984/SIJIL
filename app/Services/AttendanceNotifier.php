<?php

namespace App\Services;

use App\Mail\AttendanceQrCodesToOrganizer;
use App\Models\Event;
use App\Models\Participant;
use App\Support\AttendanceSummary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tells everyone what they need to know when an event takes attendance.
 *
 * The participant already learns about it from their confirmation email, which
 * gains an ATTENDANCE row and the scan windows. What is left is the organizer's
 * copy of the QR codes and the Telegram line, which is what this handles.
 */
class AttendanceNotifier
{
    /**
     * @return array{0: bool, 1: string}  [ok, message]
     */
    public function notify(Event $event, Participant $participant): array
    {
        $attendance = AttendanceSummary::for($event);

        if (!$attendance->configured) {
            return [
                false,
                'Attendance is enabled but no sessions have been created yet, so no QR codes could be sent.',
            ];
        }

        $done = [];

        if ($this->sendQrCodesToOrganizer($event, $attendance)) {
            $done[] = 'QR codes emailed to the organizer';
        }

        if ($this->notifyTelegram($event, $participant, $attendance)) {
            $done[] = 'Telegram notified';
        }

        return [true, $done ? implode(', ', $done) . '.' : 'Nothing to send.'];
    }

    /**
     * Email the codes once per attendance setup, not once per registration.
     *
     * Without this guard a 300-participant event would send the organizer 300
     * identical emails. The cache key includes the attendance id so recreating
     * the sessions sends a fresh copy.
     */
    protected function sendQrCodesToOrganizer(Event $event, AttendanceSummary $attendance): bool
    {
        $organizerEmail = $event->contact_email ?: $event->user?->email;

        if (!$organizerEmail) {
            Log::warning('Attendance QR email skipped: no organizer address', ['event_id' => $event->id]);

            return false;
        }

        $key = "attendance-qr-sent:{$event->id}:{$attendance->attendance->id}";

        if (Cache::has($key)) {
            return false;
        }

        try {
            (new EmailService())->sendEmail(
                $event->user_id,
                new AttendanceQrCodesToOrganizer($event, $attendance),
                $organizerEmail
            );

            // Long enough to cover the registration period for one event.
            Cache::put($key, true, now()->addDays(30));

            return true;
        } catch (\Throwable $e) {
            Log::error('Attendance QR email failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Add the attendance line to the registration notification the organizer
     * already receives on Telegram. SMS is left alone on purpose: it is a
     * character-limited channel and the detail belongs in the email.
     */
    protected function notifyTelegram(Event $event, Participant $participant, AttendanceSummary $attendance): bool
    {
        try {
            $telegram = new TelegramService();

            if (!$telegram->isEnabled()) {
                return false;
            }

            $message = "📝 <b>Attendance Enabled</b>\n\n";
            $message .= "📋 <b>Event:</b> {$event->name}\n";
            $message .= "👤 <b>Participant:</b> {$participant->name}\n";
            $message .= '📌 <b>Attendance:</b> ' . $attendance->typeLabel() . "\n";
            $message .= '🔢 <b>QR codes:</b> ' . $attendance->qrCount() . "\n";

            if (count($attendance->days) > 0) {
                $first = $attendance->days[0];
                $message .= '🕘 <b>First check-in:</b> ' . $first['date']->format('d/m/Y');

                if ($first['checkin_opens']) {
                    $message .= ' ' . $first['checkin_opens'] . ' - ' . ($first['checkin_closes'] ?? '?');
                }

                $message .= "\n";
            }

            $message .= '📅 <b>Days:</b> ' . count($attendance->days) . "\n";

            return (bool) $telegram->sendMessage($message);
        } catch (\Throwable $e) {
            Log::error('Attendance Telegram notification failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
