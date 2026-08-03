<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Services\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Three attempts, spaced further apart each time, so a subscriber restarting
     * or briefly overloaded still receives the event without us hammering it.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 300];

    public int $tries = 3;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public int $endpointId,
        public string $event,
        public array $payload,
        public string $deliveryId
    ) {
    }

    public function handle(): void
    {
        $endpoint = WebhookEndpoint::find($this->endpointId);

        // The endpoint may have been deleted, paused or auto-disabled between
        // this job being queued and it being run.
        if (! $endpoint || ! $endpoint->is_active || $endpoint->isDisabled()) {
            return;
        }

        $result = WebhookDispatcher::deliver(
            $endpoint,
            $this->event,
            $this->payload,
            $this->deliveryId,
            $this->attempts()
        );

        // Failing the job is what triggers the backoff and the retry. Every
        // attempt is already recorded, so the log shows each one separately.
        if (! $result['succeeded'] && $this->attempts() < $this->tries) {
            $this->release($this->backoff[$this->attempts() - 1] ?? 300);
        }
    }
}
