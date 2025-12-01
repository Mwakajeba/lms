<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
            
        return view('chat.index', compact('users'));
    }

    public function fetchMessages($userId)
    {
        try {
            // Verify the user exists and is in the same company
            $user = User::where('id', $userId)
                ->where('company_id', Auth::user()->company_id)
                ->firstOrFail();

        $messages = ChatMessage::where(function($q) use ($userId) {
            $q->where('sender_id', Auth::id())
              ->where('receiver_id', $userId);
        })->orWhere(function($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', Auth::id());
            })
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'asc')
            ->get();

            // Mark messages as read
            ChatMessage::where('sender_id', $userId)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'nullable|string|max:1000',
                'file' => 'nullable|file|max:10240', // 10MB max
            ]);

            // Verify the receiver is in the same company
            $receiver = User::where('id', $request->receiver_id)
                ->where('company_id', Auth::user()->company_id)
                ->firstOrFail();

            $messageData = [
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ? trim($request->message) : null,
                'is_read' => false,
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('chat-files', $fileName, 'public');
                
                $messageData['file_path'] = $filePath;
                $messageData['file_name'] = $file->getClientOriginalName();
                $messageData['file_size'] = $this->formatFileSize($file->getSize());
                $messageData['file_type'] = $file->getClientMimeType();
            }

            $message = ChatMessage::create($messageData);
            $message->load(['sender:id,name', 'receiver:id,name']);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'sender_id' => 'required|exists:users,id',
            ]);

            ChatMessage::where('sender_id', $request->sender_id)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnreadCount()
    {
        try {
            $unreadCounts = ChatMessage::where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->select('sender_id', DB::raw('count(*) as count'))
                ->groupBy('sender_id')
                ->get()
                ->keyBy('sender_id');

            return response()->json([
                'success' => true,
                'unread_counts' => $unreadCounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread counts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clearChat(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            // Verify the user is in the same company
            $user = User::where('id', $request->user_id)
                ->where('company_id', Auth::user()->company_id)
                ->firstOrFail();

            ChatMessage::where(function($q) use ($request) {
                $q->where('sender_id', Auth::id())
                  ->where('receiver_id', $request->user_id);
            })->orWhere(function($q) use ($request) {
                $q->where('sender_id', $request->user_id)
                  ->where('receiver_id', Auth::id());
            })->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chat cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOnlineUsers()
    {
        try {
            // This is a simplified version - in a real app you'd track actual online status
            $onlineUsers = User::where('id', '!=', Auth::id())
                ->where('company_id', Auth::user()->company_id)
                ->where('id', 'like', '%0') // Simple logic to show some users as online
                ->select('id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'online_users' => $onlineUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get online users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadFile($messageId)
    {
        try {
            $message = ChatMessage::where('id', $messageId)
                ->where(function($q) {
                    $q->where('sender_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
                })
                ->firstOrFail();

            if (!$message->file_path) {
                abort(404, 'File not found');
            }

            $filePath = storage_path('app/public/' . $message->file_path);
            
            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }

            // Get file extension
            $extension = strtolower(pathinfo($message->file_name, PATHINFO_EXTENSION));
            
            // Define which file types should open in browser vs download
            $browserTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'html', 'htm'];
            $shouldOpenInBrowser = in_array($extension, $browserTypes);

            if ($shouldOpenInBrowser) {
                // Open in browser
                return response()->file($filePath, [
                    'Content-Type' => $message->file_type,
                    'Content-Disposition' => 'inline; filename="' . $message->file_name . '"'
                ]);
            } else {
                // Force download
                return response()->download($filePath, $message->file_name, [
                    'Content-Type' => $message->file_type
                ]);
            }
        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }
}
