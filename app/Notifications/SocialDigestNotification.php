<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/**
 * One push for social activity on a post (ActivityDigest): a single person ("Sara commented on ...") or a summary
 * at the end of a window ("12 new comments on ...").
 */
class SocialDigestNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(private string $kind, private int $postId, private string $title, private ?string $actor, private int $count) {}

    public function via($notifiable): array
    {
        return ! empty($notifiable->fcm_token) ? [FcmChannel::class] : [];
    }

    /** @return array{0:string,1:string,2:string,3:string} [title en, body en, title ar, body ar] */
    public function texts(): array
    {
        $t = $this->title;
        $n = $this->count;
        if ($this->actor !== null) {
            $body = \App\Services\Social\ActivityDigest::text($this->kind, $this->actor, 0, $t);
            [$te, $ta] = match ($this->kind) {
                'comment' => ['New comment', 'تعليق جديد'],
                'comment_reply' => ['New reply', 'رد جديد'],
                'mention' => ['You were mentioned', 'تمت الإشارة إليك'],
                default => ['New like', 'إعجاب جديد'],
            };

            return [$te, $body['en'], $ta, $body['ar']];
        }

        return match ($this->kind) {
            'comment' => ['New comments', "$n new comments on your post \"$t\"", 'تعليقات جديدة', "$n تعليقات جديدة على منشورك \"$t\""],
            'comment_reply' => ['New replies', "$n new replies to your comment on \"$t\"", 'ردود جديدة', "$n ردود جديدة على تعليقك على \"$t\""],
            'mention' => ['Mentions', "You were mentioned $n more times on \"$t\"", 'إشارات', "تمت الإشارة إليك $n مرات أخرى في \"$t\""],
            default => ['New likes', "$n people liked your post \"$t\"", 'إعجابات جديدة', "أعجب $n أشخاص بمنشورك \"$t\""],
        };
    }

    public function toFcm($notifiable): FcmMessage
    {
        [$te, $be, $ta, $ba] = $this->texts();
        $ar = ($notifiable->locale ?? 'ar') === 'ar';

        return FcmMessage::create()
            ->data([
                'type' => $this->kind,
                'post_id' => (string) $this->postId,
                'target_type' => 'post',
                'target_id' => (string) $this->postId,
                'title_ar' => $ta,
                'body_ar' => $ba,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(\NotificationChannels\Fcm\Resources\Notification::create()->title($ar ? $ta : $te)->body($ar ? $ba : $be));
    }
}
