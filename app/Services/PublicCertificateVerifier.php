<?php

namespace App\Services;

use App\Models\Certificate;

/**
 * The one definition of what a public certificate check finds and shows.
 *
 * The /verify page and the public verification API used by the marketing site
 * both answer the same question for strangers, so they must agree on which
 * certificate a number refers to and on exactly which facts about it are public.
 * Keeping both here means a field can never appear in one and not the other.
 */
class PublicCertificateVerifier
{
    /**
     * The shape both number generators produce: a 14-digit timestamp and six
     * uppercase hex characters. Anything else cannot be a certificate number.
     */
    public const NUMBER_PATTERN = '/^CERT-\d{14}-[0-9A-F]{6}$/';

    /**
     * The API key for each row the page prints, in the page's order.
     */
    private const RECORD_KEYS = [
        'Awarded to' => 'awarded_to',
        'Event' => 'event',
        'Held on' => 'held_on',
        'Location' => 'location',
        'Issued by' => 'issued_by',
        'Issued on' => 'issued_on',
    ];

    /**
     * Look up a certificate by its exact number.
     *
     * Soft-deleted certificates are excluded by the model's SoftDeletes scope, so a
     * withdrawn certificate reads the same as one that never existed.
     */
    public function find(string $number): ?Certificate
    {
        return Certificate::query()
            ->where('certificate_number', $number)
            ->with(['event:id,name,start_date,end_date,location,user_id', 'event.user:id,name', 'participant:id,name'])
            ->first();
    }

    /**
     * The label => value rows the public page lists, with empty values dropped.
     *
     * @return array<string, string>
     */
    public function rows(Certificate $certificate): array
    {
        $event = $certificate->event;

        $dates = null;
        if ($event?->start_date) {
            $dates = $event->start_date->format('j F Y');

            if ($event->end_date && ! $event->end_date->isSameDay($event->start_date)) {
                $dates .= ' – ' . $event->end_date->format('j F Y');
            }
        }

        // Name, event, date and issuer. Nothing that could be used to
        // contact or identify the holder beyond their name.
        return array_filter([
            'Awarded to' => $certificate->participant?->name,
            'Event' => $certificate->event->name ?? null,
            'Held on' => $dates,
            'Location' => $event->location ?? null,
            'Issued by' => $event->user->name ?? null,
            'Issued on' => $certificate->generated_at?->format('j F Y'),
        ]);
    }

    /**
     * The same rows under stable keys, plus the number, for the JSON API.
     *
     * @return array<string, string>
     */
    public function publicRecord(Certificate $certificate): array
    {
        $record = [];

        foreach ($this->rows($certificate) as $label => $value) {
            $record[self::RECORD_KEYS[$label]] = $value;
        }

        $record['certificate_number'] = $certificate->certificate_number;

        return $record;
    }
}
