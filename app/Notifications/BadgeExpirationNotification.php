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
            ->setData([
                'type' => 'badge_expiration',
                'post_id' => (string) $this->postId,
                'old_badge' => $this->oldBadgeName,
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
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('badge_expiration'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#FF9800')
                            ->setSound('default')
                            ->setChannelId('badge_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('badge_expiration'))
                    ->setPayload([
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ]
                    ])
            );
    }
}
