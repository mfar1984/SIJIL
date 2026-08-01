<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HelpdeskTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'subject',
        'description',
        'user_id',
        'pwa_participant_id',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Boot function to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate ticket_id before creating a new ticket
        static::creating(function ($ticket) {
            if (!$ticket->ticket_id) {
                $latestTicket = self::orderBy('id', 'desc')->first();
                
                if ($latestTicket) {
                    // Extract the numeric part of the ticket_id
                    if (substr_count($latestTicket->ticket_id, '-') > 1) {
                        // Already using the new format (HD-DDMMYY-XXXX)
                        // Generate a new unique ID with today's date
                        $datePrefix = now()->format('dmy');
                        $uniqueCode = self::generateUniqueCode();
                        $ticket->ticket_id = "HD-{$datePrefix}-{$uniqueCode}";
                    } else {
                        // Using the old format (HD-XXXX)
                        $numericPart = (int)substr($latestTicket->ticket_id, 3);
                        $nextId = $numericPart + 1;
                        
                        // If we've reached 9999, switch to a new format with more digits
                        if ($nextId > 9999) {
                            // Format: HD-DDMMYY-XXXX where XXXX is a unique alphanumeric code
                            $datePrefix = now()->format('dmy');
                            $uniqueCode = self::generateUniqueCode();
                            $ticket->ticket_id = "HD-{$datePrefix}-{$uniqueCode}";
                        } else {
                            // Original format for tickets under 10000
                            $ticket->ticket_id = 'HD-' . $nextId;
                        }
                    }
                } else {
                    // First ticket in the system - start from 1001
                    $ticket->ticket_id = 'HD-1001';
                }
            }
        });
    }
    
    /**
     * Generate a unique alphanumeric code for ticket ID
     * Format: 4 characters, mix of uppercase letters and numbers
     * Excluding ambiguous characters like 0, O, 1, I, etc.
     */
    protected static function generateUniqueCode()
    {
        // Characters to use (excluding ambiguous ones)
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $length = 4;
        
        do {
            // Generate a random code
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
            
            // Check if this code already exists for today
            $datePrefix = now()->format('dmy');
            $exists = self::where('ticket_id', "HD-{$datePrefix}-{$code}")->exists();
        } while ($exists); // Repeat if code already exists
        
        return $code;
    }

    /**
     * Get the user who created this ticket
     *
     * Null when the ticket came from the participant app. See pwaParticipant().
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The app participant who raised this ticket, when it did not come from the
     * backend.
     *
     * A ticket carries exactly one author: either user_id or pwa_participant_id.
     */
    public function pwaParticipant()
    {
        return $this->belongsTo(PwaParticipant::class, 'pwa_participant_id');
    }

    /**
     * Whether this ticket was raised from the participant app.
     */
    public function isFromApp(): bool
    {
        return $this->pwa_participant_id !== null;
    }

    /**
     * Who raised the ticket, whichever table they live in.
     *
     * The views used to read $ticket->user->name directly, which is null for an app
     * ticket.
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->isFromApp()) {
            return $this->pwaParticipant->name ?? 'App participant';
        }

        return $this->user->name ?? 'Deleted account';
    }

    public function getAuthorEmailAttribute(): ?string
    {
        return $this->isFromApp()
            ? ($this->pwaParticipant->email ?? null)
            : ($this->user->email ?? null);
    }

    /**
     * Where the ticket came from, for a badge in the list.
     */
    public function getAuthorSourceAttribute(): string
    {
        return $this->isFromApp() ? 'App' : 'Backend';
    }

    /**
     * Tickets raised from the app.
     */
    public function scopeFromApp($query)
    {
        return $query->whereNotNull('pwa_participant_id');
    }

    /**
     * Tickets raised from the backend by a user account.
     */
    public function scopeFromBackend($query)
    {
        return $query->whereNull('pwa_participant_id');
    }

    /**
     * Get the user assigned to this ticket
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all messages for this ticket
     */
    public function messages()
    {
        return $this->hasMany(HelpdeskMessage::class, 'ticket_id');
    }

    /**
     * Get the latest message for this ticket
     */
    public function latestMessage()
    {
        return $this->hasOne(HelpdeskMessage::class, 'ticket_id')->latest();
    }

    /**
     * Get unread messages count for a specific user
     */
    public function unreadMessagesCount($userId)
    {
        // notAuthoredBy() rather than a bare `!=`, which skips app messages because
        // their user_id is null. See HelpdeskMessage::scopeNotAuthoredBy().
        return $this->messages()
            ->notAuthoredBy($userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Scope for tickets with open status
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for tickets with in_progress status
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for tickets with resolved status
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for tickets with closed status
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for tickets belonging to a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for tickets assigned to a specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
