<?php

namespace App\Http\Controllers;

use App\Models\HelpdeskTicket;
use App\Models\HelpdeskMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HelpdeskController extends Controller
{
    /**
     * Display the helpdesk dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        // Base query for all tickets
        $baseQuery = HelpdeskTicket::with(['user', 'assignedUser', 'latestMessage']);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('priority')) {
            $baseQuery->where('priority', $request->priority);
        }
        
        if ($request->filled('category')) {
            $baseQuery->where('category', $request->category);
        }
        
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        
        // For non-admin users, only show their own tickets
        if (!$isAdmin) {
            $baseQuery->where('user_id', $user->id);
        }
        
        // Clone the query for pagination
        $perPage = $request->get('per_page', 10);
        $tickets = (clone $baseQuery)->latest()->paginate($perPage);
        
        // Get tickets for different tabs (using pagination for consistency)
        $openTickets = (clone $baseQuery)->where('status', 'open')->latest()->paginate($perPage);
        $inProgressTickets = (clone $baseQuery)->where('status', 'in_progress')->latest()->paginate($perPage);
        $resolvedTickets = (clone $baseQuery)->where('status', 'resolved')->latest()->paginate($perPage);
        
        // Get counts for different statuses (using separate queries for counts)
        $openCount = (clone $baseQuery)->where('status', 'open')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'in_progress')->count();
        $resolvedCount = (clone $baseQuery)->where('status', 'resolved')->count();
        
        // For admin, get a list of users who can be assigned to tickets
        $assignableUsers = $isAdmin ? User::whereHas('roles', function($q) {
            $q->where('name', 'Administrator');
        })->get() : null;
        
        return view('helpdesk.index', [
            'tickets' => $tickets,
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
            'openCount' => $openCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount' => $resolvedCount,
            'isAdmin' => $isAdmin,
            'assignableUsers' => $assignableUsers,
        ]);
    }
    
    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'category' => 'required|in:technical,billing,event,account,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create the ticket
        $ticket = HelpdeskTicket::create([
            'subject' => $request->subject,
            'description' => $request->message,
            'user_id' => Auth::id(),
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
        ]);
        
        // Process attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('helpdesk/attachments', 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }
        
        // Create the initial message
        HelpdeskMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachments' => $attachments,
        ]);
        
        // Notify via FCM: assigned admin only (if any, and not the creator themself)
        try {
            if (!empty($ticket->assigned_to) && $ticket->assigned_to != Auth::id()) {
                $assignedAdminTokens = \App\Models\FcmToken::where('user_id', $ticket->assigned_to)->pluck('token')->all();
                app(\App\Services\FcmService::class)->sendToTokens($assignedAdminTokens, [
                    'title' => 'New Support Ticket',
                    'body' => '#'.$ticket->ticket_id.': '.$ticket->subject,
                ], [
                    'url' => route('helpdesk.show', $ticket->id),
                    'type' => 'helpdesk_ticket',
                    'ticket_id' => (string)$ticket->id,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('FCM error (NewTicketCreated): '.$e->getMessage());
        }
        
        return redirect()->route('helpdesk.show', $ticket->id)
            ->with('success', 'Ticket created successfully. Your ticket ID is ' . $ticket->ticket_id);
    }
    
    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        $ticket = HelpdeskTicket::with(['user', 'assignedUser'])->findOrFail($id);
        
        // Check if user has permission to view this ticket
        if (!$isAdmin && $ticket->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get messages, for non-admins exclude internal notes
        $messages = $ticket->messages()
            ->when(!$isAdmin, function($q) {
                return $q->where('is_internal', false);
            })
            ->with('user')
            ->orderBy('created_at')
            ->get();
        
        // Do not auto-mark messages as read here; let the user explicitly mark them
        
        // For admin, get a list of users who can be assigned to tickets
        $assignableUsers = $isAdmin ? User::whereHas('roles', function($q) {
            $q->where('name', 'Administrator');
        })->get() : null;
        
        return view('helpdesk.show', [
            'ticket' => $ticket,
            'messages' => $messages,
            'isAdmin' => $isAdmin,
            'assignableUsers' => $assignableUsers,
        ]);
    }
    
    /**
     * Add a reply to a ticket.
     */
    public function reply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        $ticket = HelpdeskTicket::findOrFail($id);
        
        // Check if user has permission to reply to this ticket
        if (!$isAdmin && $ticket->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Process attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('helpdesk/attachments', 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }
        
        // Create the message
        $message = HelpdeskMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_internal' => $isAdmin && $request->has('is_internal') ? true : false,
            'attachments' => $attachments,
        ]);
        
        // Update ticket status if needed
        if ($isAdmin && $ticket->status == 'open') {
            $ticket->update([
                'status' => 'in_progress',
                'assigned_to' => $ticket->assigned_to ?? $user->id,
            ]);
        } elseif (!$isAdmin && $ticket->status == 'resolved') {
            // If organizer replies to a resolved ticket, change status to in_progress
            $ticket->update([
                'status' => 'in_progress'
            ]);
            
            // Add a system message about the status change
            HelpdeskMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => "Ticket reopened by " . $user->name,
                'is_internal' => true,
            ]);
        }
        
        // Notify via FCM: ticket owner and assigned admin (if not internal)
        try {
            $ownerTokens = \App\Models\FcmToken::where('user_id', $ticket->user_id)->pluck('token')->all();
            if (!empty($ownerTokens) && !$message->is_internal && $message->user_id !== $ticket->user_id) {
                app(\App\Services\FcmService::class)->sendToTokens($ownerTokens, [
                    'title' => 'New Message',
                    'body' => 'Ticket #'.$ticket->ticket_id.': '.\Illuminate\Support\Str::limit($message->message, 50),
                ], [
                    'url' => route('helpdesk.show', $ticket->id),
                    'type' => 'helpdesk_message',
                    'ticket_id' => (string)$ticket->id,
                ]);
            }
            if (!$message->is_internal && !empty($ticket->assigned_to) && $ticket->assigned_to != $user->id) {
                $assignedAdminTokens = \App\Models\FcmToken::where('user_id', $ticket->assigned_to)->pluck('token')->all();
                app(\App\Services\FcmService::class)->sendToTokens($assignedAdminTokens, [
                    'title' => 'Helpdesk Update',
                    'body' => 'Ticket #'.$ticket->ticket_id.' received a reply',
                ], [
                    'url' => route('helpdesk.show', $ticket->id),
                    'type' => 'helpdesk_message',
                    'ticket_id' => (string)$ticket->id,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('FCM error (NewMessageSent): '.$e->getMessage());
        }
        
        return redirect()->route('helpdesk.show', $ticket->id)
            ->with('success', 'Reply added successfully.');
    }
    
    /**
     * Update the ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        // Only admins can update status
        if (!$isAdmin) {
            abort(403, 'Unauthorized action.');
        }
        
        $ticket = HelpdeskTicket::findOrFail($id);
        $oldStatus = $ticket->status;
        
        $updateData = ['status' => $request->status];
        
        // Set resolved_at or closed_at timestamp if applicable
        if ($request->status == 'resolved' && $oldStatus != 'resolved') {
            $updateData['resolved_at'] = now();
        }
        
        if ($request->status == 'closed' && $oldStatus != 'closed') {
            $updateData['closed_at'] = now();
        }
        
        // Update assigned user if provided
        if ($request->filled('assigned_to')) {
            $updateData['assigned_to'] = $request->assigned_to;
        }
        
        $ticket->update($updateData);
        
        // Add a system message about the status change
        HelpdeskMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $request->status)),
            'is_internal' => true,
        ]);
        
        // Notify via FCM: ticket owner and assigned admin
        try {
            $ownerTokens = \App\Models\FcmToken::where('user_id', $ticket->user_id)->pluck('token')->all();
            app(\App\Services\FcmService::class)->sendToTokens($ownerTokens, [
                'title' => 'Ticket Status Update',
                'body' => 'Ticket #'.$ticket->ticket_id.' status: '.ucfirst(str_replace('_',' ',$request->status)),
            ], [
                'url' => route('helpdesk.show', $ticket->id),
                'type' => 'helpdesk_status',
                'ticket_id' => (string)$ticket->id,
            ]);
            $ticket->refresh();
            if (!empty($ticket->assigned_to) && $ticket->assigned_to != $user->id) {
                $assignedAdminTokens = \App\Models\FcmToken::where('user_id', $ticket->assigned_to)->pluck('token')->all();
                app(\App\Services\FcmService::class)->sendToTokens($assignedAdminTokens, [
                    'title' => 'Ticket Status Update',
                    'body' => '#'.$ticket->ticket_id.' → '.ucfirst(str_replace('_',' ',$request->status)),
                ], [
                    'url' => route('helpdesk.show', $ticket->id),
                    'type' => 'helpdesk_status',
                    'ticket_id' => (string)$ticket->id,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('FCM error (TicketStatusUpdated): '.$e->getMessage());
        }
        
        return redirect()->route('helpdesk.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully.');
    }
    
    /**
     * Download an attachment.
     */
    public function downloadAttachment($messageId, $attachmentIndex)
    {
        $message = HelpdeskMessage::findOrFail($messageId);
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        // Check if user has permission to access this attachment
        if (!$isAdmin && $message->ticket->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $attachments = $message->attachments;
        
        if (!isset($attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$attachmentIndex];
        $path = $attachment['path'];
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Attachment file not found.');
        }
        
        return Storage::disk('public')->download($path, $attachment['name']);
    }
    
    /**
     * Get notifications for the authenticated user.
     */
    public function getNotifications()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        // Get recent tickets and messages as notifications
        $notifications = [];
        $unreadCount = 0;
        
        // For Admin: Only show tickets/messages assigned to this admin
        if ($isAdmin) {
            // Tickets assigned to this admin
            $tickets = HelpdeskTicket::where('assigned_to', $user->id)
            ->where('status', '!=', 'closed')
            ->latest()
            ->take(10)
            ->get();
            
            foreach ($tickets as $ticket) {
                $notifications[] = [
                    'id' => 'ticket_' . $ticket->id,
                    'title' => 'Support Ticket',
                    'message' => "#" . $ticket->ticket_id . ": " . $ticket->subject,
                    'icon' => 'help',
                    // Treat ticket items as informational; unread count is driven by messages
                    'read_at' => now()->toIso8601String(),
                    'time' => $ticket->created_at->diffForHumans(),
                    'url' => route('helpdesk.show', $ticket->id)
                ];
            }
            
            // Recent messages from tickets assigned to this admin
            $messages = HelpdeskMessage::whereHas('ticket', function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->where('status', '!=', 'closed');
            })
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('created_at', '>', now()->subDays(7))
            ->latest()
            ->take(10)
            ->get();
            
            foreach ($messages as $message) {
                $notifications[] = [
                    'id' => 'message_' . $message->id,
                    'title' => 'New Message',
                    'message' => "Ticket #" . $message->ticket->ticket_id . ": " . Str::limit($message->message, 50),
                    'icon' => 'forum',
                    'read_at' => $message->is_read ? now()->toIso8601String() : null,
                    'time' => $message->created_at->diffForHumans(),
                    'url' => route('helpdesk.show', $message->ticket_id)
                ];
                
                if (!$message->is_read) {
                    $unreadCount++;
                }
            }
        } 
        // For regular users: Get their recent tickets and responses
        else {
            // User's tickets with unread messages
            $tickets = HelpdeskTicket::where('user_id', $user->id)
                ->whereHas('messages', function($query) use ($user) {
                    $query->where('user_id', '!=', $user->id)
                          ->where('is_read', false)
                          ->where('is_internal', false);
                })
                ->latest()
                ->get();
            
            foreach ($tickets as $ticket) {
                $lastMessage = $ticket->messages()
                    ->where('user_id', '!=', $user->id)
                    ->where('is_internal', false)
                    ->latest()
                    ->first();
                
                if ($lastMessage) {
                    $notifications[] = [
                        'id' => 'message_' . $lastMessage->id,
                        'title' => 'New Reply',
                        'message' => "Ticket #" . $ticket->ticket_id . ": " . Str::limit($lastMessage->message, 50),
                        'icon' => 'forum',
                        'read_at' => $lastMessage->is_read ? now()->toIso8601String() : null,
                        'time' => $lastMessage->created_at->diffForHumans(),
                        'url' => route('helpdesk.show', $ticket->id)
                    ];
                    
                    if (!$lastMessage->is_read) {
                        $unreadCount++;
                    }
                }
                
                // Status updates
                if ($ticket->status == 'resolved' || $ticket->status == 'closed') {
                    $notifications[] = [
                        'id' => 'status_' . $ticket->id,
                        'title' => 'Ticket Status Update',
                        'message' => "Ticket #" . $ticket->ticket_id . " has been " . $ticket->status,
                        'icon' => 'update',
                        'read_at' => now()->subDays(1)->toIso8601String(), // Status items considered read by default
                        'time' => $ticket->updated_at->diffForHumans(),
                        'url' => route('helpdesk.show', $ticket->id)
                    ];
                }
            }
        }
        
        // Sort notifications by creation date
        usort($notifications, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });
        
        // Limit to 20 notifications
        $notifications = array_slice($notifications, 0, 20);
        
        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markNotificationsAsRead()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        
        if ($isAdmin) {
            // Mark all unread messages from tickets assigned to this admin as read
            HelpdeskMessage::whereHas('ticket', function($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
            // Do not auto-assign unassigned tickets here; assignment should be explicit
        } else {
            // Mark all messages in user's tickets as read
            HelpdeskMessage::whereHas('ticket', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete a ticket (admin only or owner with permission)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');
        $ticket = HelpdeskTicket::findOrFail($id);
        if (!$isAdmin && $ticket->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        // Delete messages and attachments
        foreach ($ticket->messages as $message) {
            if (is_array($message->attachments)) {
                foreach ($message->attachments as $attachment) {
                    if (!empty($attachment['path'])) {
                        \Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }
            $message->delete();
        }
        $ticket->delete();
        return redirect()->route('helpdesk.index')->with('success', 'Ticket deleted successfully.');
    }
}
