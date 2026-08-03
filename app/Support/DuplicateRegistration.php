<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Participant;

/**
 * Decides whether a person is already registered for an event.
 *
 * The rule used to be "one email per event", which broke the way people
 * actually register: a parent signs up three children, a company signs up its
 * staff, a guardian acts for someone else. All of them share one email, and all
 * of them were refused after the first entry.
 *
 * What actually makes two rows the same person is the identity document, not the
 * mailbox. So an email may appear as often as it likes, while an IC or passport
 * may appear once per event. That is simultaneously looser and tighter than
 * before: looser because families and representatives now work, tighter because
 * the same person can no longer slip in twice by using a second email address.
 *
 * Events with `skip_identity_verification` collect no document at all, so there
 * is nothing to identify anyone by. Those fall back to email plus name, which is
 * the only signal available.
 *
 * Both the public registration link and the admin form call this, because two
 * copies of a rule this fiddly will not stay in agreement.
 */
class DuplicateRegistration
{
    /**
     * Reduce an IC to comparable form: digits only, so "900101-01-1234",
     * "900101 01 1234" and "900101011234" are recognised as one number.
     */
    public static function normaliseIc(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    /**
     * Passports are alphanumeric, so only spacing and case can be discarded.
     */
    public static function normalisePassport(?string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $value) ?? '');
    }

    /**
     * Find the existing registration this person collides with.
     *
     * @param  array{name?: ?string, email?: ?string, identity_card?: ?string, passport_no?: ?string}  $person
     * @param  int|null  $ignoreParticipantId  The row being edited, which must not block itself.
     * @return array{participant: Participant, field: string, message: string}|null
     */
    public static function find(Event $event, array $person, ?int $ignoreParticipantId = null): ?array
    {
        /*
         * "Allow multiple registrations per person" on the General tab was stored
         * and read by nothing, so the switch did nothing either way. It is honoured
         * here, which is the only place the rule lives.
         *
         * Off is the shipped behaviour and the default: one document per event,
         * with an email free to repeat. On removes the check entirely rather than
         * loosening it selectively, because a half-applied identity rule is worse
         * than none.
         */
        if (\App\Support\SystemSettings::allowsMultipleRegistrations()) {
            return null;
        }

        $ic = self::normaliseIc($person['identity_card'] ?? null);
        $passport = self::normalisePassport($person['passport_no'] ?? null);

        // Soft-deleted rows sit in the Recycle Bin and must not block a
        // re-registration; the default global scope already excludes them.
        $base = fn () => Participant::query()
            ->where('event_id', $event->id)
            ->when($ignoreParticipantId, fn ($q) => $q->whereKeyNot($ignoreParticipantId));

        if ($ic !== '') {
            $clash = $base()
                ->whereRaw("REPLACE(REPLACE(REPLACE(identity_card, '-', ''), ' ', ''), '.', '') = ?", [$ic])
                ->first();

            if ($clash) {
                return [
                    'participant' => $clash,
                    'field' => 'identity_card',
                    'message' => 'This IC number is already registered for this event under the name "'
                        . $clash->name . '". Each person may only be registered once per event.',
                ];
            }
        }

        if ($passport !== '') {
            $clash = $base()
                ->whereRaw("UPPER(REPLACE(passport_no, ' ', '')) = ?", [$passport])
                ->first();

            if ($clash) {
                return [
                    'participant' => $clash,
                    'field' => 'passport_no',
                    'message' => 'This passport number is already registered for this event under the name "'
                        . $clash->name . '". Each person may only be registered once per event.',
                ];
            }
        }

        // No document was supplied. This is the normal case for events with
        // identity verification switched off, where name is all we have to tell
        // one registration from another.
        if ($ic === '' && $passport === '') {
            $email = strtolower(trim((string) ($person['email'] ?? '')));
            $name = self::normaliseName($person['name'] ?? null);

            if ($email === '' || $name === '') {
                return null;
            }

            $clash = $base()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->whereRaw("LOWER(REPLACE(TRIM(name), '  ', ' ')) = ?", [$name])
                ->first();

            if ($clash) {
                return [
                    'participant' => $clash,
                    'field' => 'name',
                    'message' => '"' . $clash->name . '" is already registered for this event with this email '
                        . 'address. To register someone else, enter their name instead.',
                ];
            }
        }

        return null;
    }

    /**
     * Collapse spacing and case so "Ali  Bin Abu" matches "ali bin abu".
     */
    protected static function normaliseName(?string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim((string) $value)) ?? '');
    }
}
