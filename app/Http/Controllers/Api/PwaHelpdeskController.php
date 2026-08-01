<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use App\Support\DeliveryAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Support tickets raised from the participant app.
 *
 * These go straight to the Administrator. An organizer never sees them: the backend
 * list scopes non-administrators to `user_id = me`, and an app ticket has no user_id
 * at all. That is deliberate - a participant's question about their certificate is
 * for whoever runs the system, not for one of the organizers whose event they
 * happened to attend.
 *
 * A ticket is kept separate from the account it belongs to, so the participant only
 * ever sees their own thread.
 */
class PwaHelpdeskController extends Controller
{
    /**
     * Categories a participant can pick.
     *
     * 'billing' exists in the column but means nothing to a participant, so it is not
     * offered.
     */
    public const CATEGORIES = ['technical', 'event', 'account', 'other'];

    /**
     * Priorities a participant can pick. 'urgent' is left for staff to set.
     */
    public const PRIORITIES = ['low', 'medium', 'high'];

    /**
     * The participant's own tickets, newest first.
     */
    public function index(Request $request)
    {
        $participant = $request->user();

        $tickets = HelpdeskTicket::where('pwa_participant_id', $participant->id)
            ->withCount(['messages as unread_count' => function ($query) use ($participant) {
                // Replies from staff that this participant has not opened yet.
                $query->where('is_internal', false)
                    ->where('is_read', false)
                    ->whereNull('pwa_participant_id');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => $tickets->map(fn ($ticket) => $this->summarise($ticket)),
                'categories' => self::CATEGORIES,
                'priorities' => self::PRIORITIES,
            ],
        ]);
    }

    /**
     * Raise a ticket.
     */
    public function store(Request $request)
    {
        $participant = $request->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'priority' => ['nullable', Rule::in(self::PRIORITIES)],
        ]);

        // Straight to the Administrator, which is what routes it into their queue and
        // their notification list.
        $administrator = DeliveryAccount::administrator();

        $ticket = HelpdeskTicket::create([
            'subject' => $validated['subject'],
            'description' => $validated['message'],
            'user_id' => null,
            'pwa_participant_id' => $participant->id,
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'assigned_to' => $administrator?->id,
        ]);

        HelpdeskMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'pwa_participant_id' => $participant->id,
            'message' => $validated['message'],
            'attachments' => [],
        ]);

        $this->notifyAdministrator($ticket, 'New Support Ticket',
            '#' . $ticket->ticket_id . ': ' . $ticket->subject);

        return response()->json([
            'success' => true,
            'message' => 'Ticket ' . $ticket->ticket_id . ' created. We will reply here.',
            'data' => ['ticket' => $this->summarise($ticket)],
        ], 201);
    }

    /**
     * One ticket and its thread.
     */
    public function show(Request $request, $ticketId)
    {
        $ticket = $this->findOwnTicket($request, $ticketId);

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        // Internal notes are staff-only and must never reach the app.
        $messages = $ticket->messages()
            ->where('is_internal', false)
            ->with(['user:id,name', 'pwaParticipant:id,name'])
            ->orderBy('created_at')
            ->get();

        // Opening the thread is what marks staff replies as read.
        HelpdeskMessage::where('ticket_id', $ticket->id)
            ->whereNull('pwa_participant_id')
            ->where('is_internal', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $this->summarise($ticket->fresh()),
                'messages' => $messages->map(fn ($message) => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'from_me' => $message->isFromApp(),
                    'author' => $message->author_name,
                    'sent_at' => $message->created_at?->toIso8601String(),
                ]),
            ],
        ]);
    }

    /**
     * Reply to the participant's own ticket.
     */
    public function reply(Request $request, $ticketId)
    {
        $ticket = $this->findOwnTicket($request, $ticketId);

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is closed. Please raise a new one.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        HelpdeskMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'pwa_participant_id' => $request->user()->id,
            'message' => $validated['message'],
            'attachments' => [],
        ]);

        // Answering a resolved ticket reopens it, the same rule the backend applies
        // when an organizer replies.
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress']);
        }

        $this->notifyAdministrator($ticket, 'Helpdesk Reply',
            '#' . $ticket->ticket_id . ': ' . Str::limit($validated['message'], 50));

        return response()->json([
            'success' => true,
            'message' => 'Reply sent.',
        ]);
    }

    /**
     * Resolve a ticket that belongs to the caller.
     *
     * Accepts either the numeric id or the HD- reference, and scopes by owner, so a
     * ticket belonging to somebody else simply does not resolve.
     */
    private function findOwnTicket(Request $request, $ticketId): ?HelpdeskTicket
    {
        return HelpdeskTicket::where('pwa_participant_id', $request->user()->id)
            ->where(function ($query) use ($ticketId) {
                $query->where('ticket_id', $ticketId);

                if (is_numeric($ticketId)) {
                    $query->orWhere('id', $ticketId);
                }
            })
            ->first();
    }

    /**
     * The shape the app renders a ticket in.
     */
    private function summarise(HelpdeskTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'reference' => $ticket->ticket_id,
            'subject' => $ticket->subject,
            'category' => $ticket->category,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'status_label' => ucfirst(str_replace('_', ' ', $ticket->status)),
            'unread_count' => (int) ($ticket->unread_count ?? 0),
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Push the update to the Administrator's devices.
     *
     * Best effort: a ticket must still be recorded when push is unavailable.
     */
    private function notifyAdministrator(HelpdeskTicket $ticket, string $title, string $body): void
    {
        try {
            $adminId = $ticket->assigned_to ?? DeliveryAccount::administrator()?->id;

            if (! $adminId) {
                return;
            }

            $tokens = FcmToken::where('user_id', $adminId)->pluck('token')->all();

            if (! $tokens) {
                return;
            }

            app(\App\Services\FcmService::class)->sendToTokens($tokens, [
                'title' => $title,
                'body' => $body,
            ], [
                'url' => route('helpdesk.show', $ticket->id),
                'type' => 'helpdesk_ticket',
                'ticket_id' => (string) $ticket->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM error (PWA helpdesk): ' . $e->getMessage());
        }
    }
}
