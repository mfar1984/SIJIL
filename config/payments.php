<?php

/*
|--------------------------------------------------------------------------
| CHIP Collect payments
|--------------------------------------------------------------------------
|
| Credentials are not configured here. The secret key, Brand ID and webhook
| public key live encrypted in the payment_settings table and are managed on
| Settings > Global Config > Payment, Administrator only.
|
| Environment variables are read only in this file so that config:cache keeps
| working.
|
*/

return [

    'chip' => [
        // Official CHIP Collect API. Override only to point a local mock at the
        // app during verification; ChipClient refuses a non-https URL outside
        // the local and testing environments.
        'base_url' => env('CHIP_BASE_URL', 'https://gate.chip-in.asia/api/v1/'),
        'timeout' => 15,
        'connect_timeout' => 5,
    ],

    // Where CHIP sends the buyer back after paying, failing or cancelling.
    'return_url' => env('PAYMENT_RETURN_URL', 'https://www.e-certificate.com.my/payment/status'),

    // Where an expired Organizer is told to renew.
    'pricing_url' => env('PAYMENT_PRICING_URL', 'https://www.e-certificate.com.my/pricing'),

    'support_email' => env('PAYMENT_SUPPORT_EMAIL', 'support@e-certificate.com.my'),

    'checkout_due_minutes' => 60,

    'unpaid_purge_days' => 30,

    'status_recheck_seconds' => 20,

];
