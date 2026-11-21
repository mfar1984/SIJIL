<?php

namespace App\Services\Payments;

/**
 * What a server-side check of one order against CHIP concluded.
 */
enum ReconcileOutcome: string
{
    case Fulfilled = 'fulfilled';
    case AlreadyFulfilled = 'already_fulfilled';
    case NeedsReview = 'needs_review';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case StillPending = 'still_pending';
    case Unreachable = 'unreachable';
    case NotFound = 'not_found';
    case NotApplicable = 'not_applicable';

    /**
     * The payment_events.result this outcome is recorded as (design §10).
     */
    public function eventResult(): string
    {
        return match ($this) {
            self::Fulfilled => 'fulfilled',
            self::AlreadyFulfilled => 'already_fulfilled',
            self::NeedsReview => 'needs_review',
            self::Cancelled => 'cancelled',
            self::Expired => 'expired',
            self::StillPending => 'not_yet_paid',
            self::Unreachable => 'error',
            self::NotFound, self::NotApplicable => 'ignored',
        };
    }

    /**
     * Admin flash message after a Re-check.
     */
    public function adminMessage(?string $chipStatus = null): string
    {
        return match ($this) {
            self::Fulfilled => 'Payment confirmed and fulfilled.',
            self::AlreadyFulfilled => 'This order was already fulfilled.',
            self::NeedsReview => 'The payment needs review. See the reason above.',
            self::Cancelled => 'CHIP reports the purchase as cancelled.',
            self::Expired => 'CHIP reports the purchase as expired.',
            self::StillPending => 'Still unpaid at CHIP (status: '.($chipStatus ?: 'unknown').').',
            self::Unreachable => 'CHIP could not be reached, try again later.',
            self::NotFound => 'CHIP does not know this purchase under the stored key.',
            self::NotApplicable => 'Nothing to check for this order.',
        };
    }
}
