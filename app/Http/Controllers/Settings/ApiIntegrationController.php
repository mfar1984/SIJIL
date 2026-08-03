<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDispatcher;
use App\Support\ApiEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Backs the API keys and Webhooks panels on the API & Integrations tab.
 *
 * Answers JSON because the tab is a single page that already talks to the server
 * with fetch. The alternative was a form per table row, and the whole tab sits
 * inside the Global Config form, where nested forms are invalid HTML.
 */
class ApiIntegrationController extends Controller
{
    // -----------------------------------------------------------------
    // API keys
    // -----------------------------------------------------------------

    public function storeKey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => ['string', Rule::in(array_keys(ApiKey::availableAbilities()))],
            'expires_in_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $expiresAt = isset($validated['expires_in_days'])
            ? now()->addDays((int) $validated['expires_in_days'])
            : null;

        $minted = ApiKey::mint(
            $validated['name'],
            $validated['abilities'] ?? [],
            auth()->id(),
            $expiresAt
        );

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($minted['key'])
            ->withProperties([
                'name' => $minted['key']->name,
                'prefix' => $minted['key']->key_prefix,
                'abilities' => $minted['key']->abilities,
                'expires_at' => optional($minted['key']->expires_at)->toDateTimeString(),
            ])
            ->log('API key generated');

        return response()->json([
            'success' => true,
            // The only time this value is ever returned. Nothing stores it.
            'secret' => $minted['secret'],
            'message' => 'Key generated. Copy it now, it cannot be shown again.',
            'key' => \App\Support\ApiSurface::keyRow($minted['key']->fresh()),
        ]);
    }

    /**
     * Issue a fresh secret for an existing key.
     *
     * Exists because the secret is only ever shown once, so without this a
     * mislaid key would have to be deleted and recreated, losing its name,
     * abilities and expiry along with any record of how long it had been in use.
     */
    public function regenerateKey(Request $request, ApiKey $apiKey): JsonResponse
    {
        if ($apiKey->hasExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'That key has expired. Generate a new one instead, so its expiry is set deliberately.',
            ], 422);
        }

        $secret = $apiKey->rotate();

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($apiKey)
            ->withProperties(['name' => $apiKey->name, 'new_prefix' => $apiKey->key_prefix])
            ->log('API key secret regenerated');

        return response()->json([
            'success' => true,
            'secret' => $secret,
            'message' => 'New secret issued for "' . $apiKey->name . '". The previous one no longer works.',
            'key' => \App\Support\ApiSurface::keyRow($apiKey->fresh()),
        ]);
    }

    public function revokeKey(Request $request, ApiKey $apiKey): JsonResponse
    {
        if ($apiKey->isRevoked()) {
            return response()->json([
                'success' => false,
                'message' => 'That key was already revoked.',
            ], 422);
        }

        $apiKey->forceFill(['revoked_at' => now()])->save();

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($apiKey)
            ->withProperties(['name' => $apiKey->name, 'prefix' => $apiKey->key_prefix])
            ->log('API key revoked');

        return response()->json([
            'success' => true,
            'message' => 'Revoked "' . $apiKey->name . '". Any system using it will start receiving 401 responses.',
            'key' => \App\Support\ApiSurface::keyRow($apiKey->fresh()),
        ]);
    }

    // -----------------------------------------------------------------
    // Webhook endpoints
    // -----------------------------------------------------------------

    public function storeWebhook(Request $request): JsonResponse
    {
        $validated = $this->validateWebhook($request);

        $endpoint = WebhookEndpoint::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            // Never taken from the request. Generated here so a secret cannot be
            // set to something guessable, and so it is unique per endpoint.
            'secret' => WebhookEndpoint::newSecret(),
            'events' => $validated['events'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($endpoint)
            ->withProperties(['name' => $endpoint->name, 'url' => $endpoint->url, 'events' => $endpoint->events])
            ->log('Webhook endpoint created');

        return response()->json([
            'success' => true,
            'message' => 'Endpoint saved.',
            'endpoint' => \App\Support\ApiSurface::webhookRow($endpoint),
            'secret' => $endpoint->secret,
        ]);
    }

    public function updateWebhook(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $validated = $this->validateWebhook($request, $webhookEndpoint);

        $webhookEndpoint->fill([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $validated['events'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Editing an auto-disabled endpoint is how an operator says they have
        // fixed the far end, so clear the failure count rather than making them
        // hunt for a separate re-enable control.
        if ($webhookEndpoint->isDisabled() && $request->boolean('is_active', true)) {
            $webhookEndpoint->consecutive_failures = 0;
            $webhookEndpoint->disabled_at = null;
        }

        $webhookEndpoint->save();

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($webhookEndpoint)
            ->withProperties(['name' => $webhookEndpoint->name, 'url' => $webhookEndpoint->url])
            ->log('Webhook endpoint updated');

        return response()->json([
            'success' => true,
            'message' => 'Endpoint updated.',
            'endpoint' => \App\Support\ApiSurface::webhookRow($webhookEndpoint->fresh()),
        ]);
    }

    public function rotateWebhookSecret(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $webhookEndpoint->forceFill(['secret' => WebhookEndpoint::newSecret()])->save();

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($webhookEndpoint)
            ->log('Webhook signing secret rotated');

        return response()->json([
            'success' => true,
            'secret' => $webhookEndpoint->secret,
            'message' => 'Secret rotated. The subscriber must be given the new value or every delivery will fail its signature check.',
        ]);
    }

    public function destroyWebhook(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $name = $webhookEndpoint->name;

        activity('security')
            ->causedBy(auth()->user())
            ->withProperties(['name' => $name, 'url' => $webhookEndpoint->url])
            ->log('Webhook endpoint deleted');

        // Deliveries cascade with the endpoint; keeping the log without the
        // endpoint it belonged to would leave rows nothing can explain.
        $webhookEndpoint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted "' . $name . '".',
        ]);
    }

    /**
     * Deliver a sample payload immediately, bypassing the queue.
     *
     * Synchronous on purpose. Queued deliveries need a worker, and if none is
     * running a queued test would look identical to a broken subscriber.
     */
    public function testWebhook(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $event = $request->input('event');

        if (! is_string($event) || ! ApiEvents::isKnown($event)) {
            $event = $webhookEndpoint->events[0] ?? ApiEvents::names()[0];
        }

        $result = WebhookDispatcher::deliver(
            $webhookEndpoint,
            $event,
            [
                'test' => true,
                'message' => 'Test delivery from ' . config('app.name'),
                'sent_by' => auth()->user()?->name,
            ],
            (string) Str::uuid()
        );

        $message = $result['succeeded']
            ? 'Endpoint responded ' . $result['status_code'] . ' in ' . $result['duration_ms'] . 'ms.'
            : 'Delivery failed: ' . $result['error'];

        if ($result['disabled']) {
            $message .= ' This endpoint has now been auto-disabled after '
                . WebhookEndpoint::FAILURE_LIMIT . ' consecutive failures.';
        }

        $delivery = \App\Models\WebhookDelivery::where('webhook_endpoint_id', $webhookEndpoint->id)
            ->latest('id')
            ->first();

        return response()->json([
            'success' => $result['succeeded'],
            'message' => $message,
            'result' => $result,
            // Returned so the delivery log and the endpoint row can update in
            // place. Reloading the page here would be the only other way to see
            // the attempt that was just made.
            'delivery' => $delivery ? \App\Support\ApiSurface::deliveryRow($delivery) : null,
            'endpoint' => \App\Support\ApiSurface::webhookRow($webhookEndpoint->fresh()),
        ]);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * @return array{name: string, url: string, events: array<int, string>}
     */
    private function validateWebhook(Request $request, ?WebhookEndpoint $existing = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            // A subscriber URL is fetched by this server, so it must be an
            // absolute http(s) URL and nothing else.
            'url' => ['required', 'string', 'max:500', 'url', 'starts_with:http://,https://'],
            'events' => 'required|array|min:1',
            'events.*' => ['string', Rule::in(ApiEvents::names())],
        ]);
    }

}
