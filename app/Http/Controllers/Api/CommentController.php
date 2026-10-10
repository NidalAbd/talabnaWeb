<?php

namespace App\Http\Controllers\Api;

use App\Services\Social\ActivityDigest;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\ServicePost;
use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    private const USER = ['user:id,user_name,name,email', 'user.photos'];

    /**
     * Comments of a post, 10 a page (2026-10-10).
     *
     * - ?sort=top (most liked, then most replied) or new (default). The post owner's pinned comment comes first.
     * - ?replies=2: each comment carries its first 2 replies; the rest come from comments/{id}/replies, 10 a page.
     *   Without it (apps before 2026-10-10) every reply is sent, as before, since those apps can't load more.
     * - likes_count, is_liked, is_pinned on every comment and reply.
     */
    public function index($postId, Request $request): JsonResponse
    {
        $preview = $request->has('replies') ? max(0, min(5, (int) $request->query('replies'))) : null;
        $query = Comment::with(self::USER)
            ->withCount('replies')
            ->where('service_post_id', $postId)
            ->whereNull('parent_id')
            ->orderByRaw('pinned_at IS NULL')
            ->orderByDesc('pinned_at');
        if ($request->query('sort') === 'top') {
            $query->orderByDesc('likes_count')->orderByDesc('replies_count');
        }
        $query->orderByDesc('created_at')->orderByDesc('id');
        if ($preview === null) {
            $query->with(['replies' => fn ($q) => $q->with(self::USER)->orderBy('created_at', 'asc')]);
        }
        $comments = $query->paginate(10);
        $items = $comments->getCollection();

        if ($preview !== null) {
            $ids = $items->pluck('id')->all();
            $first = $ids && $preview > 0
                ? Comment::query()->fromSub(
                    Comment::query()->withoutGlobalScopes()
                        ->selectRaw('comments.*, ROW_NUMBER() OVER (PARTITION BY parent_id ORDER BY created_at, id) AS reply_rank')
                        ->whereIn('parent_id', $ids),
                    'comments'
                )->where('reply_rank', '<=', $preview)->with(self::USER)->orderBy('created_at')->get()->groupBy('parent_id')
                : collect();
            foreach ($items as $c) {
                $c->setRelation('replies', ($first[$c->id] ?? collect())->each(fn ($r) => $r->makeHidden('reply_rank'))->values());
            }
        }
        self::withLikes($items->concat($items->flatMap(fn ($c) => $c->replies)));

        return response()->json($comments);
    }

    /** Sets is_liked / is_pinned on the given comments (one query). */
    private static function withLikes($comments): void
    {
        $ids = collect($comments)->pluck('id')->filter()->all();
        $liked = $ids && auth()->id()
            ? \Illuminate\Support\Facades\DB::table('comment_likes')->where('user_id', auth()->id())->whereIn('comment_id', $ids)->pluck('comment_id')->flip()
            : collect();
        foreach ($comments as $c) {
            $c->is_liked = $liked->has($c->id);
            $c->is_pinned = $c->pinned_at !== null;
            $c->likes_count = (int) ($c->likes_count ?? 0);
        }
    }

    /**
     * Get replies for a specific comment (10 a page, oldest first).
     */
    public function getReplies($commentId): JsonResponse
    {
        $replies = Comment::with(self::USER)
            ->where('parent_id', $commentId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id')
            ->paginate(10);
        self::withLikes($replies->getCollection());

        return response()->json($replies);
    }

    /** POST comments/{comment}/like: like or unlike a comment. */
    public function like(Comment $comment): JsonResponse
    {
        $uid = auth()->id();
        $deleted = \Illuminate\Support\Facades\DB::table('comment_likes')->where('comment_id', $comment->id)->where('user_id', $uid)->delete();
        if ($deleted) {
            Comment::where('id', $comment->id)->where('likes_count', '>', 0)->decrement('likes_count');
        } else {
            \Illuminate\Support\Facades\DB::table('comment_likes')->insertOrIgnore(['comment_id' => $comment->id, 'user_id' => $uid, 'created_at' => now()]);
            Comment::where('id', $comment->id)->increment('likes_count');
        }

        return response()->json(['is_liked' => ! $deleted, 'likes_count' => (int) Comment::where('id', $comment->id)->value('likes_count')]);
    }

    /** POST comments/{comment}/pin: the post owner pins one top-level comment (again to unpin). */
    public function pin(Comment $comment): JsonResponse
    {
        $ownerId = (int) ServicePost::where('id', $comment->service_post_id)->value('user_id');
        if ($ownerId !== (int) auth()->id()) {
            return response()->json(['message' => 'Only the post owner can pin a comment'], 403);
        }
        if ($comment->parent_id) {
            return response()->json(['message' => 'Replies cannot be pinned'], 422);
        }
        $pin = $comment->pinned_at === null;
        Comment::where('service_post_id', $comment->service_post_id)->whereNotNull('pinned_at')->update(['pinned_at' => null]);
        if ($pin) {
            Comment::where('id', $comment->id)->update(['pinned_at' => now()]);
        }

        return response()->json(['is_pinned' => $pin]);
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

        // Grouped per post (ActivityDigest): a busy post doesn't push its owner once per comment
        $post = ServicePost::find($comment->service_post_id);
        $actor = auth()->user();
        if ($post) {
            $notified = [];
            if (! empty($validatedData['parent_id'])) {
                $parentOwner = (int) Comment::where('id', $validatedData['parent_id'])->value('user_id');
                if ($parentOwner) {
                    ActivityDigest::record($parentOwner, 'comment_reply', $post, $actor);
                    $notified[] = $parentOwner;
                }
            } else {
                ActivityDigest::record((int) $post->user_id, 'comment', $post, $actor);
                $notified[] = (int) $post->user_id;
            }
            // @username mentions (at most 5 per comment), not twice for someone already told
            foreach (self::mentionedUserIds($validatedData['content']) as $mentioned) {
                if (! in_array($mentioned, $notified, true)) {
                    ActivityDigest::record($mentioned, 'mention', $post, $actor);
                }
            }
        }

        return response()->json($comment, 201);
    }

    /** Users named as @user_name in a comment (at most 5). */
    public static function mentionedUserIds(string $content): array
    {
        if (! preg_match_all('/(?<![\w@])@([A-Za-z0-9._]{3,30})/u', $content, $m)) {
            return [];
        }
        $names = array_slice(array_unique(array_map(fn ($n) => rtrim($n, '.'), $m[1])), 0, 5);

        return User::whereIn('user_name', $names)->where('is_active', 'active')->pluck('id')->map(fn ($id) => (int) $id)->all();
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
