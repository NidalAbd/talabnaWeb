<?php

namespace App\Services\Social;

use App\Jobs\SendSocialDigest;
use App\Models\Notification;
use App\Models\ServicePost;
use App\Models\User;
use App\Notifications\SocialDigestNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Comments, replies, likes and mentions grouped per post (2026-10-10), so a busy post doesn't send its owner a push
 * per comment (100 comments were 100 pushes and 100 notification rows).
 *
 * - The notification list keeps ONE unread row per post and kind for a day: "Sara and 12 others commented on ...".
 * - Pushes: the first comment/reply/mention pushes at once; more within WINDOW minutes are sent as one summary when
 *   the window ends ("12 new comments on ..."). Likes never push one by one: one summary per window.
 * - At most HOURLY_CAP social pushes per user per hour; the list still shows everything.
 */
class ActivityDigest
{
    public const KINDS = ['comment', 'comment_reply', 'like', 'mention'];
    public const WINDOW = ['comment' => 10, 'comment_reply' => 10, 'mention' => 10, 'like' => 30];
    public const HOURLY_CAP = 20;

    public static function record(int $recipientId, string $kind, ServicePost $post, User $actor): void
    {
        if ($recipientId === (int) $actor->id || ! in_array($kind, self::KINDS, true)) {
            return;
        }
        try {
            $count = self::listRow($recipientId, $kind, $post, $actor);
            self::push($recipientId, $kind, $post, $actor, $count);
        } catch (\Throwable $e) {
            Log::warning('activity digest failed', ['kind' => $kind, 'post' => $post->id, 'error' => $e->getMessage()]);
        }
    }

    public static function name(User $u): string
    {
        return (string) ($u->user_name ?: $u->name ?: 'Someone');
    }

    public static function title(ServicePost $post): string
    {
        $t = $post->title;
        if (is_array($t)) {
            $t = $t['ar'] ?? $t['en'] ?? reset($t) ?: '';
        }
        $t = trim((string) $t);

        return mb_strlen($t) > 30 ? mb_substr($t, 0, 30).'...' : $t;
    }

    /** @return array{en:string, ar:string} */
    public static function text(string $kind, string $actor, int $others, string $title): array
    {
        $o = $others;
        [$en, $ar] = match ($kind) {
            'comment' => $o > 0
                ? ["$actor and $o others commented on your post \"$title\"", "قام $actor و$o آخرون بالتعليق على منشورك \"$title\""]
                : ["$actor commented on your post \"$title\"", "قام $actor بالتعليق على منشورك \"$title\""],
            'comment_reply' => $o > 0
                ? ["$actor and $o others replied to your comment on \"$title\"", "قام $actor و$o آخرون بالرد على تعليقك على \"$title\""]
                : ["$actor replied to your comment on \"$title\"", "قام $actor بالرد على تعليقك على \"$title\""],
            'mention' => $o > 0
                ? ["$actor and $o others mentioned you on \"$title\"", "أشار إليك $actor و$o آخرون في \"$title\""]
                : ["$actor mentioned you in a comment on \"$title\"", "أشار إليك $actor في تعليق على \"$title\""],
            'like' => $o > 0
                ? ["$actor and $o others liked your post \"$title\"", "أعجب $actor و$o آخرون بمنشورك \"$title\""]
                : ["$actor liked your post \"$title\"", "أعجب $actor بمنشورك \"$title\""],
        };

        return ['en' => $en, 'ar' => $ar];
    }

    /** Writes or updates the list row; returns how many people are in it now. */
    private static function listRow(int $recipientId, string $kind, ServicePost $post, User $actor): int
    {
        $row = Notification::where('user_id', $recipientId)->where('type', $kind)
            ->where('target_type', 'post')->where('target_id', $post->id)
            ->where('read', false)->where('created_at', '>', now()->subDay())
            ->latest('id')->first();
        // People in the current row (keyed by recipient, kind and post; a new row starts a new list)
        $actorsKey = "digest:actors:$recipientId:$kind:{$post->id}";
        $actors = $row ? Cache::get($actorsKey, []) : [];
        $actors = array_values(array_unique(array_merge([(int) $actor->id], $actors)));
        $text = self::text($kind, self::name($actor), count($actors) - 1, self::title($post));
        $message = json_encode(['en' => $text['en']." [post_id:{$post->id}]", 'ar' => $text['ar']." [post_id:{$post->id}]"], JSON_UNESCAPED_UNICODE);

        if ($row) {
            $row->forceFill(['message' => $message])->save();
            $row->touch(); // to the top of the list
        } else {
            Notification::create([
                'user_id' => $recipientId, 'type' => $kind, 'read' => false,
                'target_type' => 'post', 'target_id' => $post->id, 'message' => $message,
            ]);
        }
        Cache::put($actorsKey, $actors, now()->addDay());

        return count($actors);
    }

    private static function windowKey(int $recipientId, string $kind, int $postId): string
    {
        return "digest:window:$recipientId:$kind:$postId";
    }

    private static function push(int $recipientId, string $kind, ServicePost $post, User $actor, int $count): void
    {
        $key = self::windowKey($recipientId, $kind, $post->id);
        $minutes = self::WINDOW[$kind];
        $opened = Cache::add($key, now()->timestamp, now()->addMinutes($minutes));
        if ($opened && $kind !== 'like') {
            // First in the window: this one goes now
            self::send($recipientId, $kind, $post, self::name($actor), 0);

            return;
        }
        // Inside a window (or a like): counted, and one summary goes when the window ends
        $pending = Cache::increment("$key:pending");
        if ($pending === 1) {
            SendSocialDigest::dispatch($recipientId, $kind, $post->id)->delay(now()->addMinutes($minutes));
        }
    }

    /** Called by SendSocialDigest when a window ends. */
    public static function flush(int $recipientId, string $kind, int $postId): void
    {
        $key = self::windowKey($recipientId, $kind, $postId);
        $pending = (int) Cache::pull("$key:pending", 0);
        Cache::forget($key);
        $post = ServicePost::find($postId);
        if ($pending <= 0 || ! $post) {
            return;
        }
        self::send($recipientId, $kind, $post, null, $pending);
    }

    private static function send(int $recipientId, string $kind, ServicePost $post, ?string $actor, int $count): void
    {
        $hour = "digest:cap:$recipientId:".now()->format('YmdH');
        Cache::add($hour, 0, now()->addHour());
        if (Cache::increment($hour) > self::HOURLY_CAP) {
            return;
        }
        $user = User::find($recipientId);
        if ($user && ! empty($user->fcm_token)) {
            $user->notify(new SocialDigestNotification($kind, $post->id, self::title($post), $actor, $count));
        }
    }
}
