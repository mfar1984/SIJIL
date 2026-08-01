<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PwaParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Creates the mobile app account that goes with a participant record.
 *
 * Previously this only happened from the admin screen, one participant at a
 * time, which is why 85 of the 93 imported accounts never received a password.
 * Events with `auto_pwa_registration` enabled now get an account created and
 * emailed at the moment the participant registers.
 */
class PwaAccount
{
    /**
     * Create (or reuse) the app account for a participant and email the
     * credentials.
     *
     * Reuse matters: the same person can register for several events with the
     * same email. In that case the existing account is attached to the new event
     * and their password is left alone, because resetting it would lock them out
     * of an account they are already using.
     *
     * @return array{created: bool, account: PwaParticipant|null, emailed: bool, message: string}
     */
    public static function createForParticipant(
        Participant $participant,
        Event $event,
        ?User $sender = null
    ): array {
        if (empty($participant->email)) {
            return [
                'created' => false,
                'account' => null,
                'emailed' => false,
                'message' => 'Participant has no email address, so no app account was created.',
            ];
        }

        $sender = $sender ?: $event->user;

        try {
            $existing = PwaParticipant::withTrashed()
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($participant->email))])
                ->first();

            if ($existing) {
                // A previously removed account is brought back rather than
                // leaving the participant unable to register at all.
                if ($existing->trashed()) {
                    $existing->restore();
                }

                self::attachEvent($existing, $event);

                if ($existing->related_participant_id === null) {
                    $existing->forceFill(['related_participant_id' => $participant->id])->saveQuietly();
                }

                return [
                    'created' => false,
                    'account' => $existing,
                    'emailed' => false,
                    'message' => 'This email already has an app account; it was linked to this event.',
                ];
            }

            $password = PwaPassword::generate($sender);

            $account = PwaParticipant::create([
                'name' => $participant->name,
                'email' => $participant->email,
                'username' => self::uniqueUsername($participant->name, $participant->email),
                'phone' => $participant->phone,
                'organization' => $participant->organization,
                'identity_card' => $participant->identity_card,
                'passport_no' => $participant->passport_no,
                'gender' => $participant->gender,
                'race' => $participant->race,
                'date_of_birth' => $participant->date_of_birth,
                'job_title' => $participant->job_title,
                'address1' => $participant->address1,
                'address2' => $participant->address2,
                'city' => $participant->city,
                'state' => $participant->state,
                'postcode' => $participant->postcode,
                'country' => $participant->country,
                'password' => Hash::make($password),
                'is_active' => true,
                'status' => 'active',
                // Left null on purpose: it marks the generated password as not
                // yet changed, which the admin list reports on.
                'password_changed_at' => null,
                'created_by' => $sender?->id,
                'updated_by' => $sender?->id,
                'related_participant_id' => $participant->id,
            ]);

            self::attachEvent($account, $event);

            $mail = PwaMailer::send(
                type: 'welcome',
                participant: $account,
                vars: [
                    'password' => $password,
                    'username' => $account->username,
                    'event_name' => $event->name,
                ],
                sender: $sender,
                fallback: [
                    'subject' => 'Welcome to E-Certificate - Your Login Details',
                    'content' => '<p><strong>Dear @{{name}},</strong></p>'
                        . '<p>You have been registered for <strong>@{{event_name}}</strong> and an account has been '
                        . 'created for you on the E-Certificate app, where you can view your events, check in with a '
                        . 'QR code and download your certificates.</p>'
                        . '<div style="background-color:#f9fafb;padding:12px;border-radius:4px;margin:16px 0">'
                        . '<p style="font-size:14px;margin:0 0 6px"><strong>Email:</strong> @{{email}}</p>'
                        . '<p style="font-size:14px;margin:0"><strong>Password:</strong> @{{password}}</p>'
                        . '</div>'
                        . '<p>Sign in at @{{login_url}} and change your password after your first sign-in.</p>'
                        . '<p style="margin-top:16px;font-size:12px;color:#6b7280">'
                        . 'Need help? Contact us at @{{support_email}}</p>',
                ]
            );

            return [
                'created' => true,
                'account' => $account,
                'emailed' => $mail['sent'],
                'message' => $mail['sent']
                    ? 'App account created and sign-in details emailed.'
                    : 'App account created, but the email failed: ' . $mail['message'],
            ];
        } catch (\Throwable $e) {
            Log::error('Auto PWA account creation failed', [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'created' => false,
                'account' => null,
                'emailed' => false,
                'message' => 'Could not create the app account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Link the account to the event without creating duplicate pivot rows.
     */
    protected static function attachEvent(PwaParticipant $account, Event $event): void
    {
        if (!$account->events()->where('events.id', $event->id)->exists()) {
            $account->events()->attach($event->id, [
                'is_registered' => true,
                'registered_at' => now(),
            ]);
        }
    }

    /**
     * Build a username that is not already taken.
     */
    public static function uniqueUsername(?string $name, ?string $email = null): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $name));

        if ($base === '' && $email) {
            $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strstr($email, '@', true) ?: ''));
        }

        $base = substr($base ?: 'user', 0, 10);

        $username = $base;
        $counter = 1;

        while (PwaParticipant::withTrashed()->where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
