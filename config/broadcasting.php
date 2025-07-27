<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when broadcasting events. You may set this to any of the
    | connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'app_id' => env('REVERB_APP_ID', 'sijil'),
            'key' => env('REVERB_APP_KEY', 'sijil_key'),
            'secret' => env('REVERB_APP_SECRET', 'sijil_secret'),
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
            'path' => env('REVERB_PATH', '/reverb/socket'),
            'options' => [
                'retry_after' => 3000,
                'enable_client_messages' => false,
                'enable_statistics' => true,
            ],
            'client_options' => [
                'reconnect_after' => 3000,
                'retry_strategy' => 'exponential',
            ],
            'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

]; 