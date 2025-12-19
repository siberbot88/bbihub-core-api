<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Send a new chat message
     * 
     * POST /api/v1/chat/send
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|string',
                'message' => 'required|string|max:5000',
                'attachment_url' => 'nullable|url',
            ]);

            $user = Auth::user();
            \Log::info('Chat send - User:', ['id' => $user->id, 'name' => $user->name]);
            
            // Determine user type based on role (with fallback)
            try {
                $userType = $user->hasRole('admin') ? 'admin' : 'owner';
            } catch (\Exception $e) {
                \Log::warning('hasRole() failed in ChatController: ' . $e->getMessage());
                $userType = 'owner'; // Default to owner
            }

            \Log::info('Creating chat message', [
                'room_id' => $request->room_id,
                'user_id' => $user->id,
                'user_type' => $userType,
            ]);

            $chatMessage = ChatMessage::create([
                'room_id' => $request->room_id,
                'user_id' => $user->id,
                'user_type' => $userType,
                'message' => $request->message,
                'attachment_url' => $request->attachment_url,
            ]);

            \Log::info('Chat message created', ['id' => $chatMessage->id]);

            // TRIGGER AI CHATBOT (Only if sender is owner)
            if ($userType === 'owner') {
                try {
                    // Get recent history for context (last 5 messages)
                    $history = ChatMessage::inRoom($request->room_id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->reverse()
                        ->map(function ($msg) {
                            return [
                                'role' => $msg->user_type === 'admin' ? 'model' : 'user',
                                'message' => $msg->message
                            ];
                        })
                        ->toArray();
                    
                    // Remove the current message from history (since it's the trigger)
                    if (!empty($history)) {
                        array_pop($history);
                    }

                    // INTERCEPT: 'Hubungi Admin' or just 'Admin' intent
                    // Log for debugging
                    \Log::info('Chat Intercept Check', ['msg' => $request->message]);

                    if (stripos(strtolower($request->message), 'admin') !== false) {
                        $aiResponse = "Anda dapat menghubungi Admin kami melalui:\n\n"
                            . "WhatsApp: 0877-2189-3340\n"
                            . "Email: admin@bbihub.com\n\n"
                            . "Jam Operasional: 09:00 - 17:00 WIB";
                    } else {
                        // Generate AI Response for other messages
                        try {
                            $chatbotService = new \App\Services\ChatbotService();
                            $aiResponse = $chatbotService->generateResponse($request->message, $history);
                        } catch (\Exception $e) {
                            \Log::error('Chatbot Service Failed: ' . $e->getMessage());
                            $aiResponse = null;
                        }
                    }
                    
                    if ($aiResponse) {
                        try {
                            // Try to find a real admin to attribute the message to
                            // This prevents FK errors if user_id is constrained
                            $adminUser = \App\Models\User::whereHas('roles', function($q) {
                                $q->where('name', 'admin');
                            })->first();

                            // Fallback to a zero UUID if no admin found (or if using simple ID)
                            $aiUserId = $adminUser ? $adminUser->id : '00000000-0000-0000-0000-000000000000'; 
                            
                            // Save AI Message as 'admin'
                            $aiMessage = ChatMessage::create([
                                'room_id' => $request->room_id,
                                'user_id' => $aiUserId, 
                                'user_type' => 'admin', // AI acts as admin
                                'message' => $aiResponse,
                                'is_read' => false,
                            ]);
                            \Log::info('AI Auto-reply sent', ['id' => $aiMessage->id]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to save AI/Admin message: ' . $e->getMessage());
                        }
                    }

                } catch (\Exception $e) {
                    \Log::error('Chatbot trigger failed: ' . $e->getMessage());
                    // Don't block the original response if AI fails
                }
            }

            $chatMessage->load('user');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $chatMessage->id,
                    'room_id' => $chatMessage->room_id,
                    'user_id' => $chatMessage->user_id,
                    'user_name' => $chatMessage->user->name,
                    'user_type' => $chatMessage->user_type,
                    'message' => $chatMessage->message,
                    'attachment_url' => $chatMessage->attachment_url,
                    'is_read' => $chatMessage->is_read,
                    'created_at' => $chatMessage->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Chat send error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get new messages after a specific timestamp (for polling)
     * 
     * GET /api/v1/chat/messages?room_id=xxx&after=2025-12-17T10:00:00Z
     */
    public function getMessages(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string',
            'after' => 'nullable|date',
        ]);

        $query = ChatMessage::with('user')
            ->inRoom($request->room_id)
            ->orderBy('created_at', 'asc');

        if ($request->has('after')) {
            $query->after($request->after);
        }

        $messages = $query->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'room_id' => $msg->room_id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user ? $msg->user->name : 'BBI Support',
                'user_type' => $msg->user_type,
                'message' => $msg->message,
                'attachment_url' => $msg->attachment_url,
                'is_read' => $msg->is_read,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Get full chat history for a room
     * 
     * GET /api/v1/chat/history?room_id=xxx&limit=50
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $request->input('limit', 50);

        $messages = ChatMessage::with('user')
            ->inRoom($request->room_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'room_id' => $msg->room_id,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user ? $msg->user->name : 'BBI Support',
                    'user_type' => $msg->user_type,
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_url,
                    'is_read' => $msg->is_read,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Mark messages as read
     * 
     * POST /api/v1/chat/mark-read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'required|integer|exists:chat_messages,id',
        ]);

        $count = ChatMessage::markMultipleAsRead($request->message_ids);

        return response()->json([
            'success' => true,
            'data' => [
                'marked_count' => $count,
            ],
        ]);
    }

    /**
     * Get all active chat rooms (Admin only)
     * 
     * GET /api/v1/chat/rooms
     */
    public function getRooms(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Get unique rooms with latest message and unread count
        $rooms = ChatMessage::selectRaw('room_id, MAX(created_at) as last_message_at')
            ->groupBy('room_id')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($room) {
                $unreadCount = ChatMessage::inRoom($room->room_id)
                    ->unread()
                    ->where('user_type', 'owner') // Only count owner messages as unread for admin
                    ->count();

                $lastMessage = ChatMessage::with('user')
                    ->inRoom($room->room_id)
                    ->latest()
                    ->first();

                return [
                    'room_id' => $room->room_id,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'user_name' => $lastMessage->user ? $lastMessage->user->name : 'BBI Support',
                        'created_at' => $lastMessage->created_at->toIso8601String(),
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ]);
    }

    /**
     * Clear chat history for a room
     *
     * DELETE /api/v1/chat/history
     */
    public function clearHistory(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string',
        ]);

        $deleted = ChatMessage::inRoom($request->room_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
            'count' => $deleted
        ]);
    }
}
