<?php

namespace App\Support;

use App\Models\PwaParticipant;

/**
 * Recognises a banned person trying to register again.
 *
 * A ban is stored on the app account, but the block has to work on the public
 * registration form where there is no account yet - only a name, an email and
 * possibly an IC or passport number. Those are matched against banned accounts.
 *
 * The ban is system-wide on purpose. A ban that only covered one organizer's
 * events would let the same person register with the next organizer, which is not
 * what banning is for.
 */
class ParticipantBan
{
    /**
     * Is this person banned?
     *
     * Matching is case-insensitive on email, and ignores dashes and spaces in
     * identity numbers, because the same IC gets typed several ways.
     *
     * @return PwaParticipant|null  the banned account, when there is one
     */
    public static function find(?string $email, ?string $identityCard = null, ?string $passportNo = null): ?PwaParticipant
    {
        $email = trim((string) $email);
        $ic = self::normaliseId($identityCard);
        $passport = self::normaliseId($passportNo);

        if ($email === '' && $ic === '' && $passport === '') {
            return null;
        }

        return PwaParticipant::query()
            ->banned()
            ->where(function ($q) use ($email, $ic, $passport) {
                if ($email !== '') {
                    $q->orWhereRaw('LOWER(email) = ?', [strtolower($email)]);
                }

                // Both sides must hold a value, otherwise every blank matches
                // every blank and one ban would block everybody.
                if ($ic !== '') {
                    $q->orWhere(function ($qq) use ($ic) {
                        $qq->whereRaw("REPLACE(REPLACE(COALESCE(identity_card, ''), '-', ''), ' ', '') = ?", [$ic])
                            ->whereRaw("COALESCE(TRIM(identity_card), '') <> ''");
                    });
                }

                if ($passport !== '') {
                    $q->orWhere(function ($qq) use ($passport) {
                        $qq->whereRaw("UPPER(REPLACE(REPLACE(COALESCE(passport_no, ''), '-', ''), ' ', '')) = ?", [strtoupper($passport)])
                            ->whereRaw("COALESCE(TRIM(passport_no), '') <> ''");
                    });
                }
            })
            ->first();
    }

    /**
     * The message shown to someone who is blocked.
     *
     * Deliberately does not repeat the stored reason: that is an internal note,
     * and the person should be talking to the organizer, not reading it off a
     * public form.
     */
    public static function message(): string
    {
        return 'Registration is not available for this email or identity number. '
            . 'Please contact the event organizer if you believe this is a mistake.';
    }

    /**
     * Strip the punctuation people vary when typing an identity number.
     */
    protected static function normaliseId(?string $value): string
    {
        return str_replace(['-', ' '], '', trim((string) $value));
    }
}
