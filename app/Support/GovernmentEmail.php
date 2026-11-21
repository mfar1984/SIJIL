<?php

namespace App\Support;

/**
 * Government package eligibility: the buyer's email domain must equal one of
 * the configured domains or be a subdomain of one. With the default "gov.my",
 * x@jpn.gov.my and x@gov.my match; x@notgov.my and x@gov.my.evil.com do not.
 */
class GovernmentEmail
{
    /**
     * @param  list<string>  $domains
     */
    public static function matches(string $email, array $domains): bool
    {
        $domain = PaymentPrivacy::emailDomain(trim($email));

        if ($domain === '') {
            return false;
        }

        foreach ($domains as $allowed) {
            $allowed = strtolower(trim((string) $allowed));

            if ($allowed === '') {
                continue;
            }

            if ($domain === $allowed || str_ends_with($domain, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }
}
