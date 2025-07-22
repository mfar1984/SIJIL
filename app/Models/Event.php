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
        'start_date',
        'end_date',
        'start_time',
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
} 