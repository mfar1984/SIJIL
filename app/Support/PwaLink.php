<?php

namespace App\Support;

use App\Models\Event;
use App\Models\PwaParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * How a PWA account is tied back to the real participant records.
 *
 * The pivot table event_pwa_participant and the related_participant_id column
 * are both empty for every account that was imported, so anything that relies
 * on them reports zero. The mobile API never used them either: it matches on
 * email (and IC as a fallback), which is why the app works while the admin
 * charts were blank. This centralises that matching rule.
 */
class PwaLink
{
    /**
     * PWA accounts visible to this user.
     *
     * Administrators see everything. An organizer sees accounts they created,
     * accounts attached to one of their events, and accounts whose email
     * matches a participant on one of their events.
     */
    public static function accountsFor(?User $user): Builder
    {
        $query = PwaParticipant::query();

        if (!$user || $user->hasRole('Administrator')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('event_pwa_participant as ep')
                        ->join('events as ev', 'ep.event_id', '=', 'ev.id')
                        ->whereColumn('ep.pwa_participant_id', 'pwa_participants.id')
                        ->whereNull('ev.deleted_at')
                        ->where('ev.user_id', $user->id);
                })
                ->orWhereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('participants as rp')
                        ->join('events as ev', 'rp.event_id', '=', 'ev.id')
                        ->whereColumn(DB::raw('LOWER(rp.email)'), DB::raw('LOWER(pwa_participants.email)'))
                        ->whereNull('rp.deleted_at')
                        ->whereNull('ev.deleted_at')
                        ->where('ev.user_id', $user->id);
                })
                // Same person, different address on file. Both sides must actually
                // hold an IC: without the emptiness guard every account with no IC
                // would match every participant with no IC.
                ->orWhereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('participants as rp')
                        ->join('events as ev', 'rp.event_id', '=', 'ev.id')
                        ->whereColumn(
                            DB::raw("REPLACE(REPLACE(rp.identity_card, '-', ''), ' ', '')"),
                            DB::raw("REPLACE(REPLACE(pwa_participants.identity_card, '-', ''), ' ', '')")
                        )
                        ->whereRaw("COALESCE(TRIM(rp.identity_card), '') <> ''")
                        ->whereRaw("COALESCE(TRIM(pwa_participants.identity_card), '') <> ''")
                        ->whereNull('rp.deleted_at')
                        ->whereNull('ev.deleted_at')
                        ->where('ev.user_id', $user->id);
                });
        });
    }

    /**
     * May this user act on this account?
     *
     * Answered with the same query that builds the list, so the two can never
     * disagree: if an account is not in your list, you cannot open, edit, reset or
     * delete it, and if it is, you can.
     */
    public static function canAccess(?User $user, PwaParticipant $account): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('Administrator')) {
            return true;
        }

        return static::accountsFor($user)
            ->withTrashed()
            ->whereKey($account->getKey())
            ->exists();
    }

    /**
     * Constrain a PWA account query to accounts reachable from one event.
     */
    public static function scopeToEvent(Builder $query, int $eventId): Builder
    {
        return $query->where(function ($q) use ($eventId) {
            $q->whereExists(function ($sub) use ($eventId) {
                $sub->select(DB::raw(1))
                    ->from('event_pwa_participant as ep')
                    ->whereColumn('ep.pwa_participant_id', 'pwa_participants.id')
                    ->where('ep.event_id', $eventId);
            })->orWhereExists(function ($sub) use ($eventId) {
                $sub->select(DB::raw(1))
                    ->from('participants as rp')
                    ->whereColumn(DB::raw('LOWER(rp.email)'), DB::raw('LOWER(pwa_participants.email)'))
                    ->whereNull('rp.deleted_at')
                    ->where('rp.event_id', $eventId);
            });
        });
    }

    /**
     * Add "this account matches at least one real participant row" to a query.
     */
    public static function whereLinkedToParticipant(Builder $query, ?iterable $eventIds = null): Builder
    {
        return $query->whereExists(function ($sub) use ($eventIds) {
            $sub->select(DB::raw(1))
                ->from('participants as rp')
                ->whereColumn(DB::raw('LOWER(rp.email)'), DB::raw('LOWER(pwa_participants.email)'))
                ->whereNull('rp.deleted_at');

            if ($eventIds !== null) {
                $sub->whereIn('rp.event_id', collect($eventIds)->all());
            }
        });
    }

    /**
     * Event ids owned by this user, or null when the user sees everything.
     */
    public static function ownedEventIds(?User $user): ?array
    {
        if (!$user || $user->hasRole('Administrator')) {
            return null;
        }

        return Event::where('user_id', $user->id)->pluck('id')->all();
    }
}
