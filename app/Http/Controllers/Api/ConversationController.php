<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Real in-app chat between users — separate from (and alongside) the
 * external contact sheet (WhatsApp/call/email). Deliberately simple:
 * polling-based on the client, no websockets/push, matching the scope
 * this needed.
 */
class ConversationController extends Controller
{
    /** List the authenticated user's conversations, most recent first. */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne.photos', 'userTwo.photos', 'servicePost:id,title'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $conversations->getCollection()->transform(function (Conversation $conversation) use ($userId) {
            return $this->formatConversation($conversation, $userId);
        });

        return response()->json($conversations);
    }

    /** Start (or fetch the existing) conversation with another user. */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|integer|exists:users,id',
            'service_post_id' => 'nullable|integer|exists:service_posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $recipientId = (int) $request->recipient_id;

        if ($recipientId === $userId) {
            return response()->json(['error' => 'Cannot start a conversation with yourself'], 422);
        }

        $conversation = Conversation::between($userId, $recipientId, $request->service_post_id);
        $conversation->load(['userOne.photos', 'userTwo.photos', 'servicePost:id,title']);

        return response()->json($this->formatConversation($conversation, $userId), 201);
    }

    /** Paginated messages for a conversation, newest first. */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        $perPage = (int) $request->input('per_page', 30);

        $messages = $conversation->messages()
            ->with('sender:id,user_name')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($messages);
    }

    /** Send a message in a conversation. */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'body' => $request->body,
        ]);

        $conversation->update([
            'last_message_body' => $request->body,
            'last_message_sender_id' => $userId,
            'last_message_at' => $message->created_at,
        ]);

        return response()->json(['message' => $message], 201);
    }

    /** Mark every message from the other participant as read. */
    public function markRead(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return in_array($userId, [$conversation->user_one_id, $conversation->user_two_id], true);
    }

    private function formatConversation(Conversation $conversation, int $userId): array
    {
        $other = $conversation->otherUser($userId);
        $unread = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $other->id,
                'user_name' => $other->user_name,
                'name' => $other->name,
                'avatar' => optional($other->photos->first())->src,
            ],
            'service_post' => $conversation->servicePost ? [
                'id' => $conversation->servicePost->id,
                'title' => $conversation->servicePost->title,
            ] : null,
            'last_message_body' => $conversation->last_message_body,
            'last_message_at' => $conversation->last_message_at,
            'last_message_is_mine' => $conversation->last_message_sender_id === $userId,
            'unread_count' => $unread,
        ];
    }
}
