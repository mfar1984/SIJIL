<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'pwa_participant_id',
        'event_id',
        'registration_date',
        'attendance_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'attendance_date' => 'datetime',
    ];

    /**
     * Get the participant for this registration
     */
    public function participant()
    {
        return $this->belongsTo(PwaParticipant::class, 'pwa_participant_id');
    }

    /**
     * Get the event for this registration
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope for active registrations
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope for attended events
     */
    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    /**
     * Scope for registered but not attended
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }
}
