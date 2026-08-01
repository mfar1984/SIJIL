<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpdeskMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'pwa_participant_id',
        'message',
        'is_internal',
        'attachments',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the ticket this message belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(HelpdeskTicket::class, 'ticket_id');
    }

    /**
     * Get the user who sent this message
     *
     * Null when the message came from the participant app.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The app participant who sent this message, when it did not come from a backend
     * account. A message carries exactly one of the two.
     */
    public function pwaParticipant()
    {
        return $this->belongsTo(PwaParticipant::class, 'pwa_participant_id');
    }

    public function isFromApp(): bool
    {
        return $this->pwa_participant_id !== null;
    }

    /**
     * Who wrote it, whichever table they live in.
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->isFromApp()) {
            return $this->pwaParticipant->name ?? 'App participant';
        }

        return $this->user->name ?? 'System';
    }

    /**
     * Whether this message was written by whoever raised the ticket.
     *
     * Used to decide which side of the thread it belongs on.
     */
    public function isFromTicketAuthor(): bool
    {
        $ticket = $this->ticket;

        if (! $ticket) {
            return false;
        }

        return $this->isFromApp()
            ? (int) $this->pwa_participant_id === (int) $ticket->pwa_participant_id
            : ($this->user_id !== null && (int) $this->user_id === (int) $ticket->user_id);
    }

    /**
     * Mark this message as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope for messages that are not internal notes
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal notes
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Messages written by anyone other than this backend account.
     *
     * Written as a scope because the obvious form, `where('user_id', '!=', $id)`,
     * silently drops every message from the participant app: those carry a null
     * user_id, and in SQL `NULL != 1` evaluates to NULL rather than true, so the row
     * fails the filter. That is why an app participant's reply never reached the
     * Administrator's notification bell and the unread count stayed at zero.
     */
    public function scopeNotAuthoredBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', '!=', $userId);
        });
    }
}
