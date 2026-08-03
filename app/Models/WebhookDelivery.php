<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_endpoint_id',
        'event',
        'delivery_id',
        'payload',
        'attempt',
        'status_code',
        'response_excerpt',
        'error',
        'duration_ms',
        'succeeded',
    ];

    protected $casts = [
        'payload' => 'array',
        'succeeded' => 'boolean',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
