<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FcmService
{
    private Client $client;
    private string $projectId;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        $this->client = new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);
    }

    public function sendToTokens(array $tokens, array $notification, array $data = []): void
    {
        if (empty($tokens)) return;

        $body = [
            'message' => [
                'token' => null, // set per token in loop
                'notification' => $notification,
                'data' => $data,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high'
                    ],
                    'fcm_options' => [
                        'link' => $data['url'] ?? '/',
                    ],
                    'notification' => [
                        'icon' => '/favicon.ico',
                    ],
                ],
            ],
        ];

        foreach ($tokens as $token) {
            $body['message']['token'] = $token;
            try {
                // Sending FCM notification
                $this->client->post(
                    sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->projectId),
                    [
                        'json' => $body,
                    ]
                );
            } catch (\Throwable $e) {
                \Log::error('FCM send error: '.$e->getMessage());
            }
        }
    }
}


