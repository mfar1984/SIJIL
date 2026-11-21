<?php

namespace App\Services\Payments;

/**
 * CHIP signs callbacks and webhooks with RSA PKCS#1 v1.5 over SHA-256 of the raw
 * body, sent base64 encoded in X-Signature (spec "Authentication").
 */
class ChipSignature
{
    public static function verify(string $rawBody, ?string $header, ?string $pem): bool
    {
        try {
            if (! is_string($header) || trim($header) === '' || ! is_string($pem) || trim($pem) === '') {
                return false;
            }

            $signature = base64_decode(trim($header), true);

            if ($signature === false || $signature === '') {
                return false;
            }

            $key = openssl_pkey_get_public($pem);

            if ($key === false) {
                return false;
            }

            return openssl_verify($rawBody, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
        } catch (\Throwable) {
            return false;
        }
    }
}
