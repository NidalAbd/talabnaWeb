<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

class PointSentFcmNotification extends Notification
{
    use Queueable;

    private int $points;
    private int $toUserId;

    public function __construct(int $points, int $toUserId)
    {
        $this->points = $points;
        $this->toUserId = $toUserId;
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
        $title = 'Points Sent Successfully';
        $titleAr = 'تم إرسال النقاط بنجاح';
        $body = "You have successfully sent {$this->points} points.";
        $bodyAr = "لقد أرسلت {$this->points} نقطة بنجاح.";

        return FcmMessage::create()
            ->data([
                'type' => 'point_sent',
                'points' => (string) $this->points,
                'to_user_id' => (string) $this->toUserId,
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
