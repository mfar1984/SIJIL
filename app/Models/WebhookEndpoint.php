<?php

namespace App\Models;

use App\Support\ApiEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WebhookEndpoint extends Model
{
    /**
     * Consecutive failures tolerated before the endpoint stops being called.
     *
     * Without this, one subscriber whose server has been decommissioned keeps
     * every future delivery queued and retried forever.
     */
    public const FAILURE_LIMIT = 10;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_delivery_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    public static function newSecret(): string
    {
        return 'whsec_' . Str::random(48);
    }

    /**
     * Endpoints that should receive the given event right now.
     */
    public static function subscribedTo(string $event): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->where('is_active', true)
            ->whereNull('disabled_at')
            ->get()
            ->filter(fn (self $endpoint) => $endpoint->listensTo($event))
            ->values();
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }

    public function isDisabled(): bool
    {
        return $this->disabled_at !== null;
    }

    /**
     * Human readable list of subscriptions, ignoring any name that is no longer
     * in the catalogue so a renamed event cannot render as a blank row.
     *
     * @return array<int, string>
     */
    public function eventLabels(): array
    {
        return array_map(
            fn (string $event) => ApiEvents::labelFor($event),
            array_filter($this->events ?? [], fn ($e) => ApiEvents::isKnown($e))
        );
    }

    public function recordSuccess(int $statusCode): void
    {
        $this->forceFill([
            'last_delivery_at' => now(),
            'last_status_code' => $statusCode,
            'consecutive_failures' => 0,
        ])->save();
    }

    /**
     * Returns true when this failure was the one that disabled the endpoint,
     * so the caller can say so rather than silently stopping.
     */
    public function recordFailure(?int $statusCode): bool
    {
        $failures = $this->consecutive_failures + 1;
        $nowDisabled = $failures >= self::FAILURE_LIMIT;

        $this->forceFill([
            'last_delivery_at' => now(),
            'last_status_code' => $statusCode,
            'consecutive_failures' => $failures,
            'disabled_at' => $nowDisabled ? now() : $this->disabled_at,
        ])->save();

        return $nowDisabled && $this->wasChanged('disabled_at');
    }

    public function reactivate(): void
    {
        $this->forceFill([
            'consecutive_failures' => 0,
            'disabled_at' => null,
            'is_active' => true,
        ])->save();
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
