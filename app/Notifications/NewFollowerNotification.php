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
            ->setData([
                'type' => 'follower',
                'follower_id' => (string) $this->followerId,
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
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('new_follower'))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#9C27B0')
                            ->setSound('default')
                            ->setChannelId('social_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('new_follower'))
                    ->setPayload(['aps' => ['sound' => 'default', 'badge' => 1]])
            );
    }
}
