<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Event extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'start_date', 'end_date', 'location', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Event {$eventName}");
    }

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
        'disable_auto_expiry',
        'skip_identity_verification',
        'auto_pwa_registration',
        'auto_generate_certificate',
        'attendance_required',
        'certificate_template_id',
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
        'disable_auto_expiry' => 'boolean',
        'skip_identity_verification' => 'boolean',
        'auto_pwa_registration' => 'boolean',
        'auto_generate_certificate' => 'boolean',
        'attendance_required' => 'boolean',
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
     * Number of places still open for public registration.
     *
     * Returns null when the event has no participant limit.
     */
    public function spotsRemaining(): ?int
    {
        if (empty($this->max_participants)) {
            return null;
        }

        return max(0, $this->max_participants - $this->participants()->count());
    }

    /**
     * Whether the event has reached its participant limit.
     *
     * Events without a limit are never full.
     */
    public function isFull(): bool
    {
        $remaining = $this->spotsRemaining();

        return $remaining !== null && $remaining <= 0;
    }

    /**
     * Check if the registration link is expired.
     *
     * @return bool
     */
    public function isRegistrationExpired()
    {
        // Always expired if status is completed
        if ($this->status === 'completed') {
            return true;
        }
        
        // If auto expiry is disabled, only check status
        if ($this->disable_auto_expiry) {
            return false;
        }
        
        // Registration expires when the event starts (default behavior)
        if ($this->start_date) {
            return now() >= $this->start_date;
        }
        
        return false;
    }

    /**
     * Relationship with the user who created this event.
     */
    /**
     * The template used when this event issues certificates automatically.
     */
    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

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
     * Relationship with certificates issued for this event.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Relationship with attendance records for this event.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get QR code for this event (for check-in).
     */
    public function getQRCode()
    {
        return $this->id . '_' . $this->registration_link;
    }
} 