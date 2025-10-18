<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'organizer',
        'description',
        'condition',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'location',
        'address',
        'max_participants',
        'status',
        'user_id',
        'contact_person',
        'contact_email',
        'contact_phone',
        'registration_link', // Unique registration link
        'registration_expires_at',
        'poster',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_expires_at' => 'datetime',
    ];

    /**
     * Prepare dates for JSON serialization (return as date strings, not timestamps)
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        // Return date in Y-m-d format for date fields, ISO timestamp for datetime fields
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the attributes that should be cast to native types (override for API responses)
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Override date serialization for API responses - return date string without timezone conversion
        if (isset($array['start_date']) && $this->start_date) {
            $array['start_date'] = $this->start_date->format('Y-m-d');
        }
        if (isset($array['end_date']) && $this->end_date) {
            $array['end_date'] = $this->end_date->format('Y-m-d');
        }
        
        return $array;
    }

    /**
     * Generate a unique registration link for the event.
     *
     * @return string
     */
    public function generateRegistrationLink()
    {
        // Generate a unique token using base64 encoding
        $uniqueToken = base64_encode(Str::uuid() . time() . $this->id);
        // Replace characters that might cause issues in URLs
        $cleanToken = str_replace(['/', '+', '='], ['_', '-', ''], $uniqueToken);
        
        $this->registration_link = $cleanToken;
        return $cleanToken;
    }

    /**
     * Check if the registration link is expired.
     *
     * @return bool
     */
    public function isRegistrationExpired()
    {
        if ($this->status === 'completed') {
            return true;
        }
        
        // Registration expires when the event starts
        if ($this->start_date) {
            return now() >= $this->start_date;
        }
        
        return false;
    }

    /**
     * Relationship with the user who created this event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with participants who registered for this event.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Relationship with PWA participants through event registrations.
     */
    public function pwaParticipants()
    {
        return $this->belongsToMany(PwaParticipant::class, 'event_pwa_participant', 'event_id', 'pwa_participant_id')
                    ->withPivot(['is_registered', 'registered_at', 'checked_in_at', 'checked_out_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Relationship with event registrations.
     */
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get QR code for this event (for check-in).
     */
    public function getQRCode()
    {
        return $this->id . '_' . $this->registration_link;
    }
} 