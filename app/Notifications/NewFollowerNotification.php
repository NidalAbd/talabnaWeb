<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

class NewFollowerNotification extends Notification
{
    use Queueable;

    private string $followerName;
    private int $followerId;

    public function __construct(string $followerName, int $followerId)
    {
        $this->followerName = $followerName;
        $this->followerId = $followerId;
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
        $title = 'New Follower';
        $titleAr = 'متابع جديد';
        $body = "{$this->followerName} started following you!";
        $bodyAr = "بدأ {$this->followerName} في متابعتك!";

        return FcmMessage::create()
            ->data([
                'type' => 'follower',
                'follower_id' => (string) $this->followerId,
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($title)
                    ->body($body)
            );
    }
}
