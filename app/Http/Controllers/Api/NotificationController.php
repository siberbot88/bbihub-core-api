<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get list of notifications
     * 
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_uuid', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get count of unread notifications
     * 
     * GET /api/v1/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        
        $count = Notification::where('user_uuid', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    /**
     * Mark notification(s) as read
     * 
     * POST /api/v1/notifications/mark-read
     * Body: { "id": "uuid" } or { "id": "all" }
     */
    public function markRead(Request $request)
    {
        $request->validate([
            'id' => 'required' // 'all' or UUID
        ]);

        $user = Auth::user();
        $id = $request->input('id');

        if ($id === 'all') {
            Notification::where('user_uuid', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
                
            $message = 'All notifications marked as read';
        } else {
            Notification::where('user_uuid', $user->id)
                ->where('id', $id)
                ->update(['is_read' => true]);
                
            $message = 'Notification marked as read';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'unread_count' => Notification::where('user_uuid', $user->id)->where('is_read', false)->count()
        ]);
    }
}
