<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Support\PwaAccount;
use Illuminate\Support\Facades\Log;

/**
 * Runs the per-event automation switches after a participant registers through
 * the public link.
 *
 * The registration itself is already committed by the time this runs, so every
 * step is wrapped: an organizer with a broken SMTP setup should not stop people
 * from registering. Each step reports what it did so the outcome is visible in
 * the log rather than failing silently.
 */
class EventAutomation
{
    /**
     * @return array<string, array{ran: bool, ok: bool, message: string}>
     */
    public static function runAfterRegistration(Event $event, Participant $participant): array
    {
        $results = [];

        // Cast explicitly: a freshly created model holds null for a column whose
        // default the database applied, so reading the attribute back before a
        // refresh gives null rather than false.
        $results['pwa_account'] = self::step(
            (bool) $event->auto_pwa_registration,
            fn () => self::createAppAccount($event, $participant)
        );

        $results['certificate'] = self::step(
            (bool) $event->auto_generate_certificate,
            fn () => self::issueCertificate($event, $participant)
        );

        $results['attendance_notice'] = self::step(
            (bool) $event->attendance_required,
            fn () => self::sendAttendanceNotices($event, $participant)
        );

        $ran = array_filter($results, fn ($r) => $r['ran']);

        if ($ran) {
            Log::info('Event automation finished', [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'results' => array_map(fn ($r) => $r['message'], $ran),
            ]);
        }

        return $results;
    }

    /**
     * Run one step only when its switch is on, and never let it throw.
     *
     * @return array{ran: bool, ok: bool, message: string}
     */
    protected static function step(bool $enabled, callable $work): array
    {
        if (!$enabled) {
            return ['ran' => false, 'ok' => true, 'message' => 'Disabled for this event.'];
        }

        try {
            [$ok, $message] = $work();

            return ['ran' => true, 'ok' => $ok, 'message' => $message];
        } catch (\Throwable $e) {
            Log::error('Event automation step failed', ['error' => $e->getMessage()]);

            return ['ran' => true, 'ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return array{0: bool, 1: string}
     */
    protected static function createAppAccount(Event $event, Participant $participant): array
    {
        $result = PwaAccount::createForParticipant($participant, $event, $event->user);

        return [$result['created'] || $result['account'] !== null, $result['message']];
    }

    /**
     * @return array{0: bool, 1: string}
     */
    protected static function issueCertificate(Event $event, Participant $participant): array
    {
        return app(CertificateIssuer::class)->issueFor($event, $participant);
    }

    /**
     * @return array{0: bool, 1: string}
     */
    protected static function sendAttendanceNotices(Event $event, Participant $participant): array
    {
        return app(AttendanceNotifier::class)->notify($event, $participant);
    }
}
