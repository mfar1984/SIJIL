<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'firebase' => [
        // Public client config, safe to expose to the browser. Read through config()
        // rather than env() so it survives `php artisan config:cache`.
        'web' => [
            'api_key' => env('VITE_FIREBASE_API_KEY'),
            'auth_domain' => env('VITE_FIREBASE_AUTH_DOMAIN'),
            'project_id' => env('VITE_FIREBASE_PROJECT_ID'),
            'storage_bucket' => env('VITE_FIREBASE_STORAGE_BUCKET'),
            'messaging_sender_id' => env('VITE_FIREBASE_MESSAGING_SENDER_ID'),
            'app_id' => env('VITE_FIREBASE_APP_ID'),
            'measurement_id' => env('VITE_FIREBASE_MEASUREMENT_ID'),
        ],

        'project_id' => env('FIREBASE_PROJECT_ID', 'e-certificate-com-my'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
