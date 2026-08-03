<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class PwaParticipant extends Authenticatable
{
    use HasFactory, HasApiTokens, SoftDeletes;

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
        'race',
        'date_of_birth',
        'status',
        'banned_at',
        'banned_by',
        'ban_reason',
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

    /*
     * date_of_birth is deliberately NOT cast, even though App\Models\Participant
     * casts its own copy to 'date'.
     *
     * Api\PwaParticipantController::profile() returns this column straight into
     * the JSON the participant app reads. Casting it would change the value from
     * "1990-05-12" to "1990-05-12T00:00:00.000000Z", which the app's profile form
     * feeds into a date input, so adding the cast breaks a client that lives in a
     * separate repository and deploys separately.
     *
     * The is_string() guard in resources/views/ecertificate/participants/edit.blade.php
     * exists for the same reason and is load-bearing, not laziness.
     *
     * email_verified_at is left alone too: nothing reads it on this model, so a
     * cast would be churn with no effect.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'banned_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    /**
     * Is this account banned?
     *
     * banned_at is the authority rather than the status enum, because status is
     * also used for ordinary active/inactive states and can be changed by the
     * edit form.
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Who applied the ban.
     */
    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Banned accounts only.
     */
    public function scopeBanned($query)
    {
        return $query->whereNotNull('banned_at');
    }

    /**
     * Accounts that are not banned.
     */
    public function scopeNotBanned($query)
    {
        return $query->whereNull('banned_at');
    }

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
     * Get the related participant (from regular participants table)
     */
    public function relatedParticipant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'related_participant_id');
    }

    /**
     * Get the attendances of the participant
     */
    public function attendances(): HasMany
    {
        // Use attendance records to reflect actual session check-ins
        return $this->hasMany(AttendanceRecord::class, 'participant_id');
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
            $q->where('events.user_id', $organizerId);
        });
    }
}
