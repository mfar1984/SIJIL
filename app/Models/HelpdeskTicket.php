<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HelpdeskTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'subject',
        'description',
        'user_id',
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
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->messages()
            ->where('user_id', '!=', $userId)
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
