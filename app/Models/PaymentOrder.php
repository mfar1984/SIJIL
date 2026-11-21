<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One checkout. The package and price are snapshots taken when the order was
 * created, so a later price edit never changes what the buyer is charged or
 * what verification expects.
 */
class PaymentOrder extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_NEEDS_REVIEW = 'needs_review';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_AWAITING_PAYMENT => 'Awaiting payment',
        self::STATUS_FULFILLED => 'Fulfilled',
        self::STATUS_NEEDS_REVIEW => 'Needs review',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_EXPIRED => 'Expired',
    ];

    public const TERMINAL = [
        self::STATUS_FULFILLED,
        self::STATUS_NEEDS_REVIEW,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'reference',
        'type',
        'status',
        'package_id',
        'package_code',
        'package_name',
        'package_category',
        'duration_months',
        'amount_cents',
        'currency',
        'email',
        'buyer_snapshot',
        'ip_hash',
        'consent_at',
        'chip_purchase_id',
        'checkout_url',
        'chip_status',
        'is_test',
        'user_id',
        'previous_expires_at',
        'package_started_at',
        'new_expires_at',
        'paid_at',
        'fulfilled_at',
        'last_checked_at',
        'set_password_sent_at',
        'purged_at',
        'failure_reason',
        'review_reason',
    ];

    protected $hidden = ['buyer_snapshot', 'ip_hash'];

    protected function casts(): array
    {
        return [
            'buyer_snapshot' => 'encrypted:array',
            'is_test' => 'boolean',
            'amount_cents' => 'integer',
            'duration_months' => 'integer',
            'consent_at' => 'datetime',
            'previous_expires_at' => 'datetime',
            'package_started_at' => 'datetime',
            'new_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'set_password_sent_at' => 'datetime',
            'purged_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function amountDisplay(): string
    {
        return Package::formatCents((int) $this->amount_cents);
    }

    /**
     * The decrypted buyer snapshot, or null when it was purged or can no longer
     * be decrypted (APP_KEY changed).
     */
    public function safeSnapshot(): ?array
    {
        try {
            return $this->buyer_snapshot;
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return null;
        }
    }
}
