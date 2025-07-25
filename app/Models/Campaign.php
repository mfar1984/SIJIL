<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

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