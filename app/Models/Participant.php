<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
        'role',
        'status',
        'event_id',
        'registration_date',
        'attendance_date',
        'notes',
        'related_participant_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registration_date' => 'datetime',
        'attendance_date' => 'datetime',
    ];

    /**
     * Relationship with the event this participant registered for.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship with the original participant if this is a registration from existing participant
     */
    public function relatedParticipant()
    {
        return $this->belongsTo(Participant::class, 'related_participant_id');
    }

    /**
     * Get registrations that were created from this participant
     */
    public function registrations()
    {
        return $this->hasMany(Participant::class, 'related_participant_id');
    }
} 