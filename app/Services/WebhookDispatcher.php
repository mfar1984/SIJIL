<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\GlobalConfig;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Support\ApiEvents;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Sends the system's outbound webhooks.
 *
 * The API tab has offered an "Enable webhooks" switch, a shared secret and a
 * comma separated event list since the beginning. Nothing was ever sent: there
 * was no endpoint to call and no code to call it. This is that code.
 */
class WebhookDispatcher
{
    /**
     * Signature header. Contains the timestamp the payload was signed at and
     * the HMAC over "timestamp.body", so a captured request cannot be replayed
     * against a different moment without breaking the signature.
     */
    public const SIGNATURE_HEADER = 'X-Sijil-Signature';
    public const EVENT_HEADER = 'X-Sijil-Event';
    public const DELIVERY_HEADER = 'X-Sijil-Delivery';

    private const TIMEOUT_SECONDS = 10;
    private const RESPONSE_EXCERPT_LENGTH = 500;

    /**
     * Queue a delivery of $event to every subscribed endpoint.
     *
     * Never throws. A webhook is a side effect of something the user asked for,
     * and a subscriber's broken server must not fail the action that triggered it.
     *
     * @param array<string, mixed> $payload
     */
    public static function dispatch(string $event, array $payload): void
    {
        try {
            if (! ApiEvents::isKnown($event)) {
                Log::warning('Refusing to dispatch unknown webhook event', ['event' => $event]);

                return;
            }

            if (! static::enabled()) {
                return;
            }

            foreach (WebhookEndpoint::subscribedTo($event) as $endpoint) {
                DeliverWebhook::dispatch($endpoint->id, $event, $payload, (string) Str::uuid());
            }
        } catch (\Throwable $e) {
            Log::error('Webhook dispatch failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Deliveries only happen when the master switch is on and the tables exist.
     */
    public static function enabled(): bool
    {
        if (! Schema::hasTable('webhook_endpoints')) {
            return false;
        }

        return (bool) (GlobalConfig::getConfig()->enable_webhooks ?? false);
    }

    /**
     * Perform one delivery attempt and record it.
     *
     * Used both by the queued job and by the "Send test" button; the button
     * calls it directly so the administrator sees the real response immediately
     * rather than waiting on a queue worker that may not be running.
     *
     * @param array<string, mixed> $payload
     * @return array{succeeded: bool, status_code: int|null, error: string|null, duration_ms: int, disabled: bool}
     */
    public static function deliver(
        WebhookEndpoint $endpoint,
        string $event,
        array $payload,
        string $deliveryId,
        int $attempt = 1
    ): array {
        $body = json_encode([
            'id' => $deliveryId,
            'event' => $event,
            'created_at' => now()->toIso8601String(),
            'data' => $payload,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $timestamp = (string) now()->getTimestamp();
        $signature = static::sign($body ?: '', $endpoint->secret, $timestamp);

        $startedAt = microtime(true);
        $statusCode = null;
        $error = null;
        $excerpt = null;

        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    self::EVENT_HEADER => $event,
                    self::DELIVERY_HEADER => $deliveryId,
                    self::SIGNATURE_HEADER => $signature,
                    'User-Agent' => 'Sijil-Webhook/1.0',
                ])
                ->timeout(self::TIMEOUT_SECONDS)
                ->withBody($body ?: '', 'application/json')
                ->post($endpoint->url);

            $statusCode = $response->status();
            $excerpt = Str::limit((string) $response->body(), self::RESPONSE_EXCERPT_LENGTH, '');

            if (! $response->successful()) {
                $error = 'Endpoint responded ' . $statusCode;
            }
        } catch (\Throwable $e) {
            // A connection refused, a DNS failure or a timeout all land here and
            // must read as a failed delivery rather than as an application error.
            $error = class_basename($e) . ': ' . Str::limit($e->getMessage(), 200, '');
        }

        $duration = (int) round((microtime(true) - $startedAt) * 1000);
        $succeeded = $error === null;

        WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event' => $event,
            'delivery_id' => $deliveryId,
            'payload' => $payload,
            'attempt' => $attempt,
            'status_code' => $statusCode,
            'response_excerpt' => $excerpt,
            'error' => $error,
            'duration_ms' => $duration,
            'succeeded' => $succeeded,
        ]);

        $disabled = false;

        if ($succeeded) {
            $endpoint->recordSuccess((int) $statusCode);
        } else {
            $disabled = $endpoint->recordFailure($statusCode);
        }

        return [
            'succeeded' => $succeeded,
            'status_code' => $statusCode,
            'error' => $error,
            'duration_ms' => $duration,
            'disabled' => $disabled,
        ];
    }

    /**
     * The signature a subscriber must reproduce to trust a payload.
     *
     * Format: t=<unix>,v1=<hex hmac of "t.body">
     */
    public static function sign(string $body, string $secret, string $timestamp): string
    {
        $digest = hash_hmac('sha256', $timestamp . '.' . $body, $secret);

        return 't=' . $timestamp . ',v1=' . $digest;
    }

    /**
     * Verify a signature produced by sign(). Exposed so the behaviour can be
     * asserted directly, and so subscribers have a reference implementation.
     */
    public static function verify(string $body, string $secret, string $header): bool
    {
        $parts = [];

        foreach (explode(',', $header) as $segment) {
            if (! str_contains($segment, '=')) {
                continue;
            }

            [$key, $value] = explode('=', trim($segment), 2);
            $parts[$key] = $value;
        }

        if (! isset($parts['t'], $parts['v1'])) {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $parts['t'] . '.' . $body, $secret),
            $parts['v1']
        );
    }
}
