<?php

namespace App\Models\Concerns;

use App\Services\WebhookDispatcher;

/**
 * Emits outbound webhooks from the model layer.
 *
 * Hooking the model rather than each controller means every path is covered:
 * the admin form, the public registration link, bulk import, auto-assign and
 * the API all create the same records, and a controller-by-controller approach
 * would have missed some of them and drifted as new paths were added.
 *
 * Each consuming model declares which event names it emits and what the payload
 * looks like.
 */
trait FiresWebhooks
{
    public static function bootFiresWebhooks(): void
    {
        static::created(function ($model) {
            if ($event = $model->webhookEventFor('created')) {
                WebhookDispatcher::dispatch($event, $model->webhookPayload());
            }
        });

        static::updated(function ($model) {
            if ($event = $model->webhookEventFor('updated')) {
                WebhookDispatcher::dispatch($event, $model->webhookPayload());
            }
        });
    }

    /**
     * The webhook event name for a lifecycle moment, or null when this model
     * does not publish that moment.
     */
    public function webhookEventFor(string $moment): ?string
    {
        return $this->webhookEvents()[$moment] ?? null;
    }

    /**
     * @return array<string, string> keyed by 'created' and/or 'updated'
     */
    abstract public function webhookEvents(): array;

    /**
     * The body delivered to subscribers.
     *
     * Keep this to identifiers and non-sensitive fields. Subscribers that need
     * more should call the v1 API with a key whose abilities say so, which is
     * auditable; a webhook payload is not.
     *
     * @return array<string, mixed>
     */
    abstract public function webhookPayload(): array;
}
