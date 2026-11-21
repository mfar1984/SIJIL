<?php

namespace App\Support;

/**
 * Keyed hashes for rate-limiter keys and the order's ip_hash, so no raw IP
 * address or email lands in the cache table or in payment_orders.
 */
class PaymentPrivacy
{
    public static function hash(#[\SensitiveParameter] string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /**
     * The part after the last "@", lowercased. Safe to log.
     */
    public static function emailDomain(string $email): string
    {
        $at = strrpos($email, '@');

        return strtolower($at === false ? '' : substr($email, $at + 1));
    }
}
