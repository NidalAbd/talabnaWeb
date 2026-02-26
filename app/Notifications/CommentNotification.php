<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class CommentNotification extends Notification
{
    use Queueable;

    private string $commenterName;
    private string $postTitle;
    private int $postId;
    private bool $isReply;

    public function __construct(string $commenterName, string $postTitle, int $postId, bool $isReply = false)
    {
        $this->commenterName = $commenterName;
        $this->postTitle = $postTitle;
        $this->postId = $postId;
        $this->isReply = $isReply;
    }

    public function via($notifiable): array
    {
        if (!empty($notifiable->fcm_token)) {
            return [FcmChannel::class];
        }
        return [];
    }

    public function toFcm($notifiable): FcmMessage
    {
        if ($this->isReply) {
            $title = 'New Reply';
            $titleAr = 'رد جديد';
            $body = "{$this->commenterName} replied to your comment on \"{$this->postTitle}\"";
            $bodyAr = "قام {$this->commenterName} بالرد على تعليقك على \"{$this->postTitle}\"";
        } else {
            $title = 'New Comment';
            $titleAr = 'تعليق جديد';
            $body = "{$this->commenterName} commented on your post \"{$this->postTitle}\"";
            $bodyAr = "قام {$this->commenterName} بالتعليق على منشورك \"{$this->postTitle}\"";
        }

        return FcmMessage::create()
            ->setData([
                'type' => $this->isReply ? 'comment_reply' : 'comment',
                'post_id' => (string) $this->postId,
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body)
                ->setImage('https://talbna.cloud/img/logo.png'))
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('comment'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#2196F3')
                            ->setSound('default')
                            ->setChannelId('comments_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('comment'))
                    ->setPayload(['aps' => ['sound' => 'default', 'badge' => 1]])
            );
    }
}
