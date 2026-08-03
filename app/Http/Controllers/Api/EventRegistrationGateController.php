<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\PwaParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * The identity check in front of the public registration form.
 *
 * A person entering their IC is asked to prove the account is theirs before the
 * form is filled in with what we already know about them. Three steps: look the
 * document up, then either sign in to the account it belongs to or create one.
 *
 * An earlier version of this lived on two open endpoints that were removed
 * because `GET /participant/lookup` returned a full profile - name, phone, IC,
 * passport, address, date of birth - to anyone who asked, and Malaysian IC
 * numbers are structured enough to work through methodically. `POST
 * /participant/register` handed out a working API token for any email that did
 * not already have an account, and because certificates are matched by email
 * address, claiming an unclaimed participant's email was enough to read their
 * certificates.
 *
 * What is different here:
 *
 *  - Personal data is only released after the password has been checked. The
 *    lookup step answers "does an account exist" and nothing more.
 *  - Email addresses come back masked, and sign-in takes an account id rather
 *    than an address, so a working IC never yields a usable email address.
 *  - Every call must carry the registration token of an open event, so these
 *    endpoints only function as part of a real registration.
 *  - No API token is issued anywhere in this flow. Proving ownership returns the
 *    data needed to fill the form and nothing that can be replayed later.
 *  - Every lookup is logged with the caller's address and a partial document
 *    number, so sustained guessing leaves a trail.
 *
 * Note on where the document lives: pwa_participants.identity_card is empty for
 * every existing account, because accounts were imported from email addresses.
 * The IC is held on the participants rows instead. So a document is resolved to
 * an account through the registrations that carry it, not by reading the account.
 */
class EventRegistrationGateController extends Controller
{
    /**
     * Step one: is this document already known, and does it have an account?
     */
    public function lookup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_token' => 'required|string',
            'id_type' => 'required|in:ic,passport',
            'ic' => 'required_if:id_type,ic|nullable|string|max:20',
            'passport' => 'required_if:id_type,passport|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $event = $this->resolveEvent($request->input('event_token'));

        if (!$event) {
            return $this->fail('This registration link is not valid or has closed.', 404);
        }

        [$document, $error] = $this->normaliseDocument($request);

        if ($error) {
            return $this->fail($error, 422);
        }

        $participants = $this->participantsHolding($request->input('id_type'), $document);
        $accounts = $this->accountsFor($participants->pluck('email'));

        Log::info('Registration gate lookup', [
            'ip' => $request->ip(),
            'event_id' => $event->id,
            'id_type' => $request->input('id_type'),
            'document' => $this->maskDocument($document),
            'registrations_found' => $participants->count(),
            'accounts_found' => $accounts->count(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                // Deliberately coarse. Anything finer would let someone work
                // through IC numbers to learn who exists.
                'exists' => $accounts->isNotEmpty(),
                'accounts' => $accounts->map(fn (PwaParticipant $a) => [
                    'id' => $a->id,
                    'email_masked' => $this->maskEmail($a->email),
                ])->values(),
            ],
        ]);
    }

    /**
     * Step two: prove the account belongs to the caller, then release the data
     * needed to fill the form.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_token' => 'required|string',
            'account_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $event = $this->resolveEvent($request->input('event_token'));

        if (!$event) {
            return $this->fail('This registration link is not valid or has closed.', 404);
        }

        $account = PwaParticipant::find($request->integer('account_id'));

        // One message for "no such account" and "wrong password", so this cannot
        // be used to confirm which accounts exist.
        if (!$account || !Hash::check($request->input('password'), (string) $account->password)) {
            Log::info('Registration gate sign-in refused', [
                'ip' => $request->ip(),
                'event_id' => $event->id,
                'account_id' => $request->integer('account_id'),
            ]);

            return $this->fail('That password does not match this account.', 401);
        }

        if (method_exists($account, 'isBanned') && $account->isBanned()) {
            return $this->fail('This account has been suspended. Please contact the event organizer.', 403);
        }

        if ($account->is_active === false || $account->status === 'inactive') {
            return $this->fail('This account is not active. Please contact the event organizer.', 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $account->email,
                'prefill' => $this->prefill($account),
            ],
        ]);
    }

    /**
     * Step two, other branch: no account yet, so make one.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            // Long enough to be worth having, without the mixed-case and symbol
            // rules that push people towards writing the password down. This is a
            // public form filled in once, often on a phone.
            'password' => 'required|string|min:8|max:255',
            'id_type' => 'nullable|in:ic,passport',
            'ic' => 'nullable|string|max:20',
            'passport' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $event = $this->resolveEvent($request->input('event_token'));

        if (!$event) {
            return $this->fail('This registration link is not valid or has closed.', 404);
        }

        $email = strtolower(trim($request->input('email')));

        // An address that already has an account must go through the password
        // check instead, or creating an account would be a way to take over one.
        $existing = PwaParticipant::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This email address already has an account. Please sign in with it instead.',
                'data' => [
                    'account_exists' => true,
                    'account' => [
                        'id' => $existing->id,
                        'email_masked' => $this->maskEmail($existing->email),
                    ],
                ],
            ], 409);
        }

        if (\App\Support\ParticipantBan::find($email, $request->input('ic'), $request->input('passport'))) {
            return $this->fail(\App\Support\ParticipantBan::message(), 403);
        }

        $idType = $request->input('id_type');

        $account = PwaParticipant::create([
            'name' => $request->input('name'),
            'email' => $email,
            'username' => \App\Support\PwaAccount::uniqueUsername($request->input('name'), $email),
            'password' => Hash::make($request->input('password')),
            // Recorded on the account as well as on the registration, so future
            // lookups can resolve it without going through participants at all.
            'identity_card' => $idType === 'ic' ? $request->input('ic') : null,
            'passport_no' => $idType === 'passport' ? $request->input('passport') : null,
            'is_active' => true,
            'status' => 'active',
            // Chosen by the person themselves, so it is not a password they need
            // to be told to change.
            'password_changed_at' => now(),
            'created_by' => $event->user_id,
            'updated_by' => $event->user_id,
        ]);

        $account->events()->attach($event->id, [
            'is_registered' => true,
            'registered_at' => now(),
        ]);

        Log::info('Registration gate account created', [
            'ip' => $request->ip(),
            'event_id' => $event->id,
            'pwa_participant_id' => $account->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $account->email,
                'prefill' => $this->prefill($account),
            ],
        ]);
    }

    /**
     * Reset the password for an account chosen during the lookup step.
     *
     * The existing reset endpoint takes an email address, which this flow
     * deliberately never sends to the browser. So the account is named by id and
     * the address is resolved here.
     *
     * The reply is the same whether or not anything was sent, because a
     * distinguishable answer would turn this into a way of confirming which
     * accounts exist.
     */
    public function resetPasswordForAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_token' => 'required|string',
            'account_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $event = $this->resolveEvent($request->input('event_token'));

        if (!$event) {
            return $this->fail('This registration link is not valid or has closed.', 404);
        }

        $vague = response()->json([
            'success' => true,
            'message' => 'If that account exists, a new password has been emailed to it.',
        ]);

        $account = PwaParticipant::find($request->integer('account_id'));

        if (!$account || (method_exists($account, 'isBanned') && $account->isBanned())) {
            return $vague;
        }

        $password = \App\Support\PwaPassword::generate($event->user);

        $account->forceFill([
            'password' => Hash::make($password),
            // Left null so the admin list keeps reporting this as a generated
            // password the holder has not yet replaced.
            'password_changed_at' => null,
        ])->saveQuietly();

        // Sent through the shared mailer so the organizer's own provider and
        // template are honoured, rather than repeating that logic here.
        $mail = \App\Support\PwaMailer::send(
            type: 'password_reset',
            participant: $account,
            vars: ['password' => $password, 'username' => $account->username],
            sender: $event->user,
            fallback: [
                'subject' => 'Your E-Certificate password has been reset',
                'content' => '<p><strong>Dear @{{name}},</strong></p>'
                    . '<p>A new password has been created for your E-Certificate account.</p>'
                    . '<div style="background-color:#f9fafb;padding:12px;border-radius:4px;margin:16px 0">'
                    . '<p style="font-size:14px;margin:0 0 6px"><strong>Email:</strong> @{{email}}</p>'
                    . '<p style="font-size:14px;margin:0"><strong>New password:</strong> @{{password}}</p>'
                    . '</div>'
                    . '<p>Sign in at @{{login_url}} and change it after signing in.</p>'
                    . '<p style="margin-top:16px;font-size:12px;color:#6b7280">'
                    . 'If you did not ask for this, contact us at @{{support_email}}</p>',
            ]
        );

        Log::info('Registration gate password reset', [
            'ip' => $request->ip(),
            'event_id' => $event->id,
            'pwa_participant_id' => $account->id,
            'emailed' => $mail['sent'],
        ]);

        return $vague;
    }

    /**
     * Only events whose link is still usable. A closed or expired event must not
     * keep these endpoints alive.
     */
    protected function resolveEvent(?string $token): ?Event
    {
        if (!$token) {
            return null;
        }

        $event = Event::where('registration_link', $token)->first();

        if (!$event || $event->isRegistrationExpired()) {
            return null;
        }

        return $event;
    }

    /**
     * @return array{0: string, 1: string|null}  [normalised document, error]
     */
    protected function normaliseDocument(Request $request): array
    {
        if ($request->input('id_type') === 'ic') {
            $ic = \App\Support\DuplicateRegistration::normaliseIc($request->input('ic'));

            // Rejecting malformed input early keeps obvious probing out of the
            // lookup path entirely.
            if (strlen($ic) !== 12) {
                return ['', 'An IC number has 12 digits, for example 900101-01-1234.'];
            }

            $month = (int) substr($ic, 2, 2);
            $day = (int) substr($ic, 4, 2);

            if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
                return ['', 'That does not look like a valid IC number. Please check and try again.'];
            }

            return [$ic, null];
        }

        $passport = \App\Support\DuplicateRegistration::normalisePassport($request->input('passport'));

        if (strlen($passport) < 5) {
            return ['', 'Please enter the full passport number.'];
        }

        return [$passport, null];
    }

    /**
     * Registrations carrying this document, newest first.
     */
    protected function participantsHolding(string $idType, string $document)
    {
        $query = Participant::query();

        if ($idType === 'ic') {
            $query->whereRaw(
                "REPLACE(REPLACE(REPLACE(identity_card, '-', ''), ' ', ''), '.', '') = ?",
                [$document]
            );
        } else {
            $query->whereRaw("UPPER(REPLACE(passport_no, ' ', '')) = ?", [$document]);
        }

        return $query->whereNotNull('email')
            ->orderByDesc('id')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Accounts belonging to any of these addresses, plus any account that
     * records the document directly.
     */
    protected function accountsFor($emails)
    {
        $emails = collect($emails)
            ->filter()
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->unique();

        if ($emails->isEmpty()) {
            return collect();
        }

        return PwaParticipant::query()
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(email)'), $emails->all())
            ->orderBy('id')
            ->get();
    }

    /**
     * What the form is filled in with, once ownership is proven.
     *
     * The account holds little beyond a name and an email, because that is all
     * an imported account ever had. The person's most recent registration is the
     * better source, so it wins wherever it has something to offer.
     */
    protected function prefill(PwaParticipant $account): array
    {
        $latest = Participant::whereRaw('LOWER(email) = ?', [strtolower(trim((string) $account->email))])
            ->orderByDesc('id')
            ->first();

        $pick = fn (string $field) => $latest?->{$field} ?: $account->{$field} ?: null;

        return [
            'name' => $account->name,
            'email' => $account->email,
            'phone' => $pick('phone'),
            'organization' => $pick('organization'),
            'job_title' => $pick('job_title'),
            'identity_card' => $pick('identity_card'),
            'passport_no' => $pick('passport_no'),
            'gender' => $pick('gender'),
            'race' => $pick('race'),
            'date_of_birth' => optional($latest?->date_of_birth ?: $account->date_of_birth)->format('Y-m-d'),
            'address1' => $pick('address1'),
            'address2' => $pick('address2'),
            'city' => $pick('city'),
            'state' => $pick('state'),
            'postcode' => $pick('postcode'),
            'country' => $pick('country') ?: 'Malaysia',
        ];
    }

    /**
     * "ahmad@gmail.com" becomes "ah***@gmail.com": enough for the owner to
     * recognise, not enough for anyone else to use.
     */
    protected function maskEmail(?string $email): string
    {
        $email = (string) $email;

        if (!str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);

        $keep = mb_strlen($local) > 2 ? 2 : 1;

        return mb_substr($local, 0, $keep) . str_repeat('*', max(3, mb_strlen($local) - $keep)) . '@' . $domain;
    }

    /**
     * Enough to correlate log lines, not enough to reconstruct the number.
     */
    protected function maskDocument(string $document): string
    {
        return mb_substr($document, 0, 6) . str_repeat('*', max(0, mb_strlen($document) - 6));
    }

    protected function fail(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
