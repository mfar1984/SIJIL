<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * The full notification list, as a page.
     *
     * "View all notifications" in the bell used to link to the helpdesk, because
     * this controller only ever spoke JSON and there was no page to send anyone to.
     * The helpdesk also needs helpdesk.read, so for a user without that permission
     * the link led to a refusal.
     */
    public function page(Request $request)
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->when($request->get('filter') === 'unread', fn ($q) => $q->unread())
            ->when($request->get('filter') === 'read', fn ($q) => $q->read())
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => in_array($request->get('filter'), ['unread', 'read'], true) ? $request->get('filter') : 'all',
            'counts' => [
                'all' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::where('user_id', $user->id)->unread()->count(),
                'read' => Notification::where('user_id', $user->id)->read()->count(),
            ],
        ]);
    }

    /**
     * The feed the bell polls. JSON only.
     */
    public function feed(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'notifications' => [],
                    'unreadCount' => 0
                ]);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'icon' => $notification->icon,
                        'read_at' => $notification->read_at ? $notification->read_at->toIso8601String() : null,
                        'time' => $notification->created_at->diffForHumans(),
                        // Reduced to a path, so following it cannot leave this host.
                        'url' => $notification->safe_url,
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->unread()
                ->count();

            return response()->json([
                'notifications' => $notifications,
                'unreadCount' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());

            return response()->json([
                'notifications' => [],
                'unreadCount' => 0
            ]);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();

            $count = Notification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);

            // The bell calls this with fetch and wants JSON; the notifications page
            // posts a form and needs to land back on the page rather than looking at
            // a JSON body.
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'marked' => $count]);
            }

            return back()->with('success', $count === 1
                ? '1 notification marked as read.'
                : "{$count} notifications marked as read.");

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Could not mark them as read: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->markAsRead();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }

            return back();

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->delete();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Notification deleted.');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
