<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PwaParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'organization',
        'address',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'login_attempts',
        'locked_until',
        'created_by',
        'updated_by',
        'related_participant_id',
        // Additional fields for full participant info
        'identity_card',
        'passport_no',
        'gender',
        'date_of_birth',
        'job_title',
        'address1',
        'address2',
        'city',
        'state',
        'postcode',
        'country',
        'notes',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    /**
     * Get the events that the participant is registered for
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_pwa_participant', 'pwa_participant_id', 'event_id')
            ->withPivot(['is_registered', 'registered_at', 'checked_in_at', 'checked_out_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the certificates earned by the participant
     */
    public function certificates(): HasMany
    {
        // Use related_participant_id to get certificates from the original participant
        return $this->hasMany(Certificate::class, 'participant_id', 'related_participant_id');
    }

    /**
     * Get the attendances of the participant
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'participant_id');
    }

    /**
     * Get the user who created this participant
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this participant
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the participant is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if the participant needs to change password
     */
    public function needsPasswordChange(): bool
    {
        return !$this->password_changed_at;
    }

    /**
     * Scope to get active participants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get participants by organizer
     */
    public function scopeByOrganizer($query, $organizerId)
    {
        return $query->whereHas('events', function($q) use ($organizerId) {
            $q->where('organizer_id', $organizerId);
        });
    }
}
