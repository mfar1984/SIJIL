<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One inbound CHIP callback or webhook, or one server-side re-check.
 *
 * The payload holds the buyer's email and name, so it is encrypted at rest and
 * purged with the order's buyer snapshot.
 */
class PaymentEvent extends Model
{
    public const SOURCES = ['callback' => 'Callback', 'webhook' => 'Webhook', 'recheck' => 'Re-check'];

    /**
     * Results after which a same-channel delivery with the same dedupe_key is a
     * replay. error, order_not_ready, not_yet_paid and rejected are never final,
     * so a CHIP retry after a non-2xx answer is processed again.
     */
    public const FINAL_RESULTS = [
        'fulfilled',
        'already_fulfilled',
        'needs_review',
        'cancelled',
        'expired',
        'ignored',
        'recorded',
    ];

    public const RESULTS = [
        'fulfilled', 'already_fulfilled', 'replayed', 'needs_review', 'recorded', 'ignored',
        'rejected', 'order_not_ready', 'not_yet_paid', 'cancelled', 'expired', 'error',
    ];

    protected $fillable = [
        'payment_order_id',
        'source',
        'event_type',
        'chip_purchase_id',
        'signature_valid',
        'result',
        'message',
        'payload',
        'dedupe_key',
        'ip_address',
    ];

    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'payload' => 'encrypted',
            'signature_valid' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id');
    }

    /**
     * The decrypted payload, or null when purged or unreadable.
     */
    public function safePayload(): ?string
    {
        try {
            return $this->payload;
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return null;
        }
    }
}
