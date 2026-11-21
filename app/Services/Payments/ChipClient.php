<?php

namespace App\Services\Payments;

use App\Models\PaymentSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CHIP Collect API client (https://docs.chip-in.asia/openapi/chip-collect.yaml).
 *
 * Laravel's Http client with a bearer token; no SDK. There are no automatic
 * retries, because a retried POST /purchases/ could create two purchases.
 *
 * The secret key is a #[\SensitiveParameter] so it never appears in a stack
 * trace, and it is only ever sent in the Authorization header, never in a URL.
 */
class ChipClient
{
    private string $baseUrl;

    private string $secretKey;

    public function __construct(string $baseUrl, #[\SensitiveParameter] string $secretKey)
    {
        $this->secretKey = $secretKey;
        $baseUrl = rtrim($baseUrl, '/').'/';

        if (! str_starts_with($baseUrl, 'https://') && ! app()->environment('local', 'testing')) {
            throw new ChipException('CHIP base URL must use https.', 0, null, 'configure');
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * A client for the stored settings.
     */
    public static function fromSettings(?PaymentSetting $settings = null): self
    {
        $settings ??= PaymentSetting::current();

        $secret = $settings->secret_key;

        if (! is_string($secret) || $secret === '') {
            throw new ChipException('No CHIP secret key is stored.', 0, null, 'configure');
        }

        return new self((string) config('payments.chip.base_url'), $secret);
    }

    /**
     * POST /purchases/
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function createPurchase(array $body, ?string $orderReference = null): array
    {
        $response = $this->send('createPurchase', fn (PendingRequest $r) => $r->post('purchases/', $body), $orderReference);

        $data = $response->json();

        if (! is_array($data) || ! is_string($data['id'] ?? null) || ! is_string($data['checkout_url'] ?? null)) {
            throw $this->fail('createPurchase', 'CHIP returned an unexpected purchase body.', $response->status(), null, $orderReference);
        }

        return $data;
    }

    /**
     * GET /purchases/{id}/
     *
     * @return array<string, mixed>
     */
    public function getPurchase(string $id, ?string $orderReference = null): array
    {
        if (! preg_match('/^[0-9a-f-]{36}$/i', $id)) {
            throw new ChipException('Invalid CHIP purchase id.', 0, null, 'getPurchase');
        }

        $response = $this->send('getPurchase', fn (PendingRequest $r) => $r->get('purchases/'.$id.'/'), $orderReference);

        $data = $response->json();

        if (! is_array($data) || ($data['id'] ?? null) === null) {
            throw $this->fail('getPurchase', 'CHIP returned an unexpected purchase body.', $response->status(), null, $orderReference);
        }

        return $data;
    }

    /**
     * GET /public_key/ : the company key that signs success callbacks. The body
     * is a JSON-encoded PEM string.
     */
    public function publicKey(): string
    {
        $response = $this->send('publicKey', fn (PendingRequest $r) => $r->get('public_key/'));

        $pem = json_decode($response->body(), true);

        if (! is_string($pem) || openssl_pkey_get_public($pem) === false) {
            throw $this->fail('publicKey', 'CHIP returned a public key that could not be read.', $response->status());
        }

        return $pem;
    }

    /**
     * GET /payment_methods/ : a harmless read that proves the key and Brand ID.
     *
     * @return array<string, mixed>
     */
    public function paymentMethods(string $brandId): array
    {
        $response = $this->send('paymentMethods', fn (PendingRequest $r) => $r->get('payment_methods/', [
            'brand_id' => $brandId,
            'currency' => 'MYR',
            'amount' => 1000,
        ]));

        $data = $response->json();

        if (! is_array($data)) {
            throw $this->fail('paymentMethods', 'CHIP returned an unexpected payment methods body.', $response->status());
        }

        return $data;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('payments.chip.timeout', 15))
            ->connectTimeout((int) config('payments.chip.connect_timeout', 5));
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     */
    private function send(string $operation, callable $call, ?string $orderReference = null): Response
    {
        try {
            $response = $call($this->request());
        } catch (ConnectionException) {
            throw $this->fail($operation, 'CHIP could not be reached.', 0, null, $orderReference);
        } catch (\Throwable $e) {
            throw $this->fail($operation, 'CHIP request failed ('.class_basename($e).').', 0, null, $orderReference);
        }

        if (! $response->successful()) {
            $code = $response->json('__all__.0.code') ?? $response->json('__all__.code');
            $code = is_string($code) ? substr($code, 0, 64) : null;

            $kind = match (true) {
                in_array($response->status(), [401, 403], true) => ' (authentication)',
                $response->status() === 404 => ' (not found)',
                default => '',
            };

            throw $this->fail($operation, 'CHIP returned HTTP '.$response->status().$kind.'.', $response->status(), $code, $orderReference);
        }

        return $response;
    }

    private function fail(string $operation, string $message, int $status, ?string $code = null, ?string $orderReference = null): ChipException
    {
        Log::warning('CHIP request failed', [
            'op' => $operation,
            'status' => $status,
            'code' => $code,
            'order' => $orderReference,
        ]);

        return new ChipException($message, $status, $code, $operation);
    }
}
