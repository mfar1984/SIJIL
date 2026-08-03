<?php

namespace App\Support;

/**
 * The canonical catalogue of webhook events.
 *
 * The API tab used to hold these in a free-text comma separated field, which
 * meant a typo produced an event name that could never fire and nothing told
 * the administrator. Subscriptions are now picked from this list.
 */
class ApiEvents
{
    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function all(): array
    {
        return [
            'event.created' => [
                'label' => 'Event created',
                'description' => 'A new event has been published.',
            ],
            'event.updated' => [
                'label' => 'Event updated',
                'description' => 'An event\'s details, dates or venue changed.',
            ],
            'registration.completed' => [
                'label' => 'Registration completed',
                'description' => 'A participant finished registering for an event.',
            ],
            'certificate.generated' => [
                'label' => 'Certificate generated',
                'description' => 'A certificate was issued to a participant.',
            ],
            'attendance.recorded' => [
                'label' => 'Attendance recorded',
                'description' => 'A participant checked in or out of a session.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(static::all());
    }

    public static function labelFor(string $event): string
    {
        return static::all()[$event]['label'] ?? $event;
    }

    public static function isKnown(string $event): bool
    {
        return array_key_exists($event, static::all());
    }
}
