<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Campaign extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Delivery channels.
     *
     * Only these two exist. The column used to accept 'whatsapp' as well, but no
     * form offered it and the processor had no branch for it, so such a campaign
     * was marked complete having sent nothing.
     */
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';

    /**
     * Lifecycle.
     *
     * draft      - saved, never queued
     * scheduled  - queued for a future moment
     * running    - due, being processed
     * completed  - finished
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Who the campaign goes to.
     */
    public const AUDIENCE_ALL = 'all_participants';
    public const AUDIENCE_EVENT = 'specific_event';
    public const AUDIENCE_FILTER = 'custom_filter';
    public const AUDIENCE_EMAILS = 'custom_emails';

    public const SCHEDULE_NOW = 'now';
    public const SCHEDULE_LATER = 'scheduled';

    public static function types(): array
    {
        return [self::TYPE_EMAIL, self::TYPE_SMS];
    }

    public static function audiences(): array
    {
        return [self::AUDIENCE_ALL, self::AUDIENCE_EVENT, self::AUDIENCE_FILTER, self::AUDIENCE_EMAILS];
    }

    public static function scheduleTypes(): array
    {
        return [self::SCHEDULE_NOW, self::SCHEDULE_LATER];
    }

    /**
     * Whether the campaign can still be started by hand.
     */
    public function isSendable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        // 'type' and 'sent_count' were logged for years and are not columns, so
        // nothing was ever recorded for them.
        return LogOptions::defaults()
            ->logOnly(['name', 'campaign_type', 'status', 'scheduled_at', 'delivered_count'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Campaign {$eventName}");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'campaign_type',
        'audience_type',
        'event_id',
        'filter_criteria',
        'start_date',
        'end_date',
        'status',
        'content',
        'schedule_type',
        'scheduled_at',
        'recipients_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filter_criteria' => 'array',
        'content' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event associated with the campaign.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the open rate percentage.
     */
    public function getOpenRateAttribute()
    {
        if ($this->delivered_count == 0) {
            return 0;
        }
        
        return round(($this->opened_count / $this->delivered_count) * 100, 1);
    }

    /**
     * Get the click rate percentage.
     */
    public function getClickRateAttribute()
    {
        if ($this->opened_count == 0) {
            return 0;
        }
        
        return round(($this->clicked_count / $this->opened_count) * 100, 1);
    }
    
    /**
     * Scope a query to only include campaigns of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('campaign_type', $type);
    }
    
    /**
     * Scope a query to only include campaigns with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include campaigns for the current user.
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
    
    /**
     * Scope a query to only include campaigns for a specific event.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }
} 