<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'notifications' => [],
                    'unreadCount' => 0
                ]);
            }
            
            // Get user's notifications
            $notifications = Notification::where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'icon' => $notification->icon,
                        'read_at' => $notification->read_at ? $notification->read_at->toIso8601String() : null,
                        'time' => $notification->created_at->diffForHumans(),
                        'url' => $notification->url ?? '#'
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
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            Notification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if ($notification) {
                $notification->markAsRead();
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if ($notification) {
                $notification->delete();
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
