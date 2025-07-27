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
        $tickets = (clone $baseQuery)->latest()->paginate(10);
        
        // Get tickets for different tabs
        $openTickets = (clone $baseQuery)->where('status', 'open')->latest()->get();
        $inProgressTickets = (clone $baseQuery)->where('status', 'in_progress')->latest()->get();
        $resolvedTickets = (clone $baseQuery)->where('status', 'resolved')->latest()->get();
        
        // Get counts for different statuses
        $openCount = $openTickets->count();
        $inProgressCount = $inProgressTickets->count();
        $resolvedCount = $resolvedTickets->count();
        
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
        
        // Broadcast the new ticket event (admin only)
        try {
            \Log::info('Broadcasting NewTicketCreated event for ticket ID: ' . $ticket->id);
            event(new \App\Events\NewTicketCreated($ticket));
        } catch (\Exception $e) {
            \Log::error('Broadcasting error for NewTicketCreated: ' . $e->getMessage());
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
        
        // Mark unread messages as read
        foreach ($messages as $message) {
            if (!$message->is_read && $message->user_id != $user->id) {
                $message->markAsRead();
            }
        }
        
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
        
        // Broadcast the new message event
        try {
            broadcast(new \App\Events\NewMessageSent($message))->toOthers();
        } catch (\Exception $e) {
            \Log::error('Broadcasting error: ' . $e->getMessage());
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
        
        // Broadcast the status change event
        try {
            broadcast(new \App\Events\TicketStatusUpdated($ticket))->toOthers();
        } catch (\Exception $e) {
            \Log::error('Broadcasting error: ' . $e->getMessage());
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
        
        // For Admin: Get all unassigned tickets, recent messages, and status updates
        if ($isAdmin) {
            // Unassigned tickets (or tickets assigned to this admin)
            $tickets = HelpdeskTicket::where(function($query) use ($user) {
                $query->whereNull('assigned_to')
                      ->orWhere('assigned_to', $user->id);
            })
            ->where('status', '!=', 'closed')
            ->latest()
            ->take(10)
            ->get();
            
            foreach ($tickets as $ticket) {
                $notificationRead = $ticket->assigned_to === $user->id;
                
                $notifications[] = [
                    'id' => 'ticket_' . $ticket->id,
                    'title' => 'Support Ticket',
                    'message' => "#" . $ticket->ticket_id . ": " . $ticket->subject,
                    'icon' => 'help',
                    'read_at' => $notificationRead ? now()->toISOString() : null,
                    'time' => $ticket->created_at->diffForHumans(),
                    'url' => route('helpdesk.show', $ticket->id)
                ];
                
                if (!$notificationRead) {
                    $unreadCount++;
                }
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
                $notificationRead = $message->is_read;
                
                $notifications[] = [
                    'id' => 'message_' . $message->id,
                    'title' => 'New Message',
                    'message' => "Ticket #" . $message->ticket->ticket_id . ": " . Str::limit($message->message, 50),
                    'icon' => 'forum',
                    'read_at' => $notificationRead ? now()->toISOString() : null,
                    'time' => $message->created_at->diffForHumans(),
                    'url' => route('helpdesk.show', $message->ticket_id)
                ];
                
                if (!$notificationRead) {
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
                        'read_at' => $lastMessage->is_read ? now()->toISOString() : null,
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
                        'read_at' => now()->subDays(1)->toISOString(), // Assume this is read to avoid clutter
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
            
            // Mark all unassigned tickets as "seen" by assigning them to this admin
            HelpdeskTicket::whereNull('assigned_to')
                ->where('status', 'open')
                ->update(['assigned_to' => $user->id]);
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
}
