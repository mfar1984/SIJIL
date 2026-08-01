<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Event;
use Carbon\Carbon;

/**
 * Reads an event's attendance setup into a shape that emails, Telegram messages
 * and the QR mailer can all use.
 *
 * An event can have attendance switched on before the organizer has actually
 * created the sessions, so every caller has to cope with "attendance is required
 * but not set up yet". That distinction is made once, here.
 */
class AttendanceSummary
{
    public function __construct(
        public bool $required,
        public bool $configured,
        public string $type,
        public array $days,
        public ?Attendance $attendance = null,
    ) {
    }

    public static function for(Event $event): self
    {
        $required = (bool) $event->attendance_required;

        $attendance = Attendance::with('sessions')
            ->where('event_id', $event->id)
            ->latest('id')
            ->first();

        if (!$attendance || $attendance->sessions->isEmpty()) {
            return new self($required, false, 'single', []);
        }

        $days = [];

        foreach ($attendance->sessions->groupBy('date') as $date => $sessions) {
            $checkin = $sessions->firstWhere('session_type', 'checkin');
            $checkout = $sessions->firstWhere('session_type', 'checkout');

            $days[] = [
                'date' => Carbon::parse($date),
                'checkin_opens' => self::time($checkin?->checkin_start_time),
                'checkin_closes' => self::time($checkin?->checkin_end_time),
                'checkout_opens' => self::time($checkout?->checkout_start_time),
                'checkout_closes' => self::time($checkout?->checkout_end_time),
                'checkin_code' => $checkin?->unique_code,
                'checkout_code' => $checkout?->unique_code,
            ];
        }

        usort($days, fn ($a, $b) => $a['date'] <=> $b['date']);

        return new self(
            $required,
            true,
            $attendance->attendance_type ?: 'single',
            $days,
            $attendance
        );
    }

    /**
     * Does this setup include a check-out window?
     */
    public function hasCheckout(): bool
    {
        foreach ($this->days as $day) {
            if ($day['checkout_opens']) {
                return true;
            }
        }

        return false;
    }

    /**
     * How many QR codes the organizer should expect.
     */
    public function qrCount(): int
    {
        $count = 0;

        foreach ($this->days as $day) {
            if ($day['checkin_code']) {
                $count++;
            }
            if ($day['checkout_code']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * A short sentence for the participant's confirmation email.
     */
    public function participantNotice(): string
    {
        if (!$this->required) {
            return '';
        }

        if (!$this->configured) {
            return 'Attendance will be taken. The organizer will share the check-in details before the event.';
        }

        return match ($this->type) {
            'daily' => 'Attendance will be taken each day of the event.',
            'custom' => 'Attendance will be taken during the sessions listed below.',
            default => 'Attendance will be taken once during the event.',
        };
    }

    /**
     * A one-line description of the mode, for the organizer's email and Telegram.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'daily' => 'Scan every day (one QR code per day)',
            'custom' => 'Custom sessions (organizer set the windows)',
            default => 'Scan once (one QR code for the whole event)',
        };
    }

    /**
     * Normalise a stored time to HH:MM, or null when it is not set.
     */
    protected static function time($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
