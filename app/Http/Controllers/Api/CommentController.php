<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get comments for a post with threaded replies.
     */
    public function index($postId): JsonResponse
    {
        // Get only top-level comments (not replies), with their replies loaded
        $comments = Comment::with([
            'user:id,user_name,name,email',
            'user.photos',
            'replies' => function ($query) {
                $query->with(['user:id,user_name,name,email', 'user.photos'])
                    ->orderBy('created_at', 'asc');
            }
        ])
            ->where('service_post_id', $postId)
            ->whereNull('parent_id') // Only top-level comments
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add replies_count to each comment
        $comments->getCollection()->transform(function ($comment) {
            $comment->replies_count = $comment->replies->count();
            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * Get replies for a specific comment.
     */
    public function getReplies($commentId): JsonResponse
    {
        $replies = Comment::with(['user:id,user_name,name,email', 'user.photos'])
            ->where('parent_id', $commentId)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json($replies);
    }

    /**
     * Store a new comment or reply.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'service_post_id' => 'required|exists:service_posts,id',
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Add the user_id from the currently authenticated user
        $validatedData['user_id'] = auth()->id();

        $comment = Comment::create($validatedData);

        // Load the user relationship for the response
        $comment->load(['user:id,user_name,name,email', 'user.photos']);

        // Send notification for reply
        if (isset($validatedData['parent_id']) && $validatedData['parent_id']) {
            $this->sendReplyNotification($comment, $validatedData['parent_id']);
        } else {
            // Send notification to post owner for new comment
            $this->sendCommentNotification($comment);
        }

        return response()->json($comment, 201);
    }

    /**
     * Send notification when someone replies to a comment.
     */
    private function sendReplyNotification(Comment $reply, int $parentCommentId): void
    {
        $parentComment = Comment::with('user')->find($parentCommentId);

        if (!$parentComment || $parentComment->user_id === auth()->id()) {
            return; // Don't notify yourself
        }

        $replierName = auth()->user()->user_name ?? auth()->user()->name ?? 'Someone';
        $post = ServicePost::find($reply->service_post_id);
        $postTitle = $post ? mb_substr($post->title, 0, 30) . (mb_strlen($post->title) > 30 ? '...' : '') : 'a post';

        Notification::create([
            'user_id' => $parentComment->user_id,
            'message' => json_encode([
                'en' => "{$replierName} replied to your comment on \"{$postTitle}\" [post_id:{$reply->service_post_id}]",
                'ar' => "قام {$replierName} بالرد على تعليقك على \"{$postTitle}\" [post_id:{$reply->service_post_id}]",
            ]),
            'type' => 'comment_reply',
            'read' => false,
        ]);
    }

    /**
     * Send notification when someone comments on a post.
     */
    private function sendCommentNotification(Comment $comment): void
    {
        $post = ServicePost::find($comment->service_post_id);

        if (!$post || $post->user_id === auth()->id()) {
            return; // Don't notify yourself
        }

        $commenterName = auth()->user()->user_name ?? auth()->user()->name ?? 'Someone';
        $postTitle = mb_substr($post->title, 0, 30) . (mb_strlen($post->title) > 30 ? '...' : '');

        Notification::create([
            'user_id' => $post->user_id,
            'message' => json_encode([
                'en' => "{$commenterName} commented on your post \"{$postTitle}\" [post_id:{$comment->service_post_id}]",
                'ar' => "قام {$commenterName} بالتعليق على منشورك \"{$postTitle}\" [post_id:{$comment->service_post_id}]",
            ]),
            'type' => 'comment',
            'read' => false,
        ]);
    }

    /**
     * Display a specific comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        $comment->load([
            'user:id,user_name,name,email',
            'user.photos',
            'replies' => function ($query) {
                $query->with(['user:id,user_name,name,email', 'user.photos'])
                    ->orderBy('created_at', 'asc');
            }
        ]);

        return response()->json($comment);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Ensure user owns this comment
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update([
            'content' => $validatedData['content'],
        ]);

        $comment->load(['user:id,user_name,name,email', 'user.photos']);

        return response()->json($comment);
    }

    /**
     * Delete a comment and its replies.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Ensure user owns this comment
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the comment (cascade will delete replies due to foreign key)
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
