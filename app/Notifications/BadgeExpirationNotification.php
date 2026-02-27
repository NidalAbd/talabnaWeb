<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

class BadgeExpirationNotification extends Notification
{
    use Queueable;

    private string $oldBadgeName;
    private int $postId;

    public function __construct(string $oldBadgeName, int $postId)
    {
        $this->oldBadgeName = $oldBadgeName;
        $this->postId = $postId;
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
        $title = 'Badge Expired';
        $titleAr = 'انتهت صلاحية الشارة';
        $body = "Your {$this->oldBadgeName} badge on post #{$this->postId} has expired and changed to normal.";
        $bodyAr = "انتهت صلاحية شارة {$this->oldBadgeName} على المنشور #{$this->postId} وتم تغييرها إلى عادي.";

        return FcmMessage::create()
            ->data([
                'type' => 'badge_expiration',
                'post_id' => (string) $this->postId,
                'old_badge' => $this->oldBadgeName,
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
