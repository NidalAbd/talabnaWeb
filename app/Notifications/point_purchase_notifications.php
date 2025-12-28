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

class point_purchase_notifications extends Notification
{
    use Queueable;

    private string $status;
    private int $points;
    private ?string $requestId;

    /**
     * Create a new notification instance.
     *
     * @param string $status 'approved' or 'cancelled'
     * @param int $points Number of points
     * @param string|null $requestId Optional request ID for reference
     */
    public function __construct(string $status, int $points, ?string $requestId = null)
    {
        $this->status = $status;
        $this->points = $points;
        $this->requestId = $requestId;
    }

    public function via($notifiable): array
    {
        // Only send FCM if user has a token
        if (!empty($notifiable->fcm_token)) {
            return [FcmChannel::class];
        }
        return [];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $logoImageUrl = 'https://talbna.cloud/img/logo.png';

        if ($this->status === 'approved') {
            $title = 'Points Added Successfully!';
            $titleAr = 'تمت إضافة النقاط بنجاح!';
            $body = "Your purchase of {$this->points} points has been approved and added to your account.";
            $bodyAr = "تمت الموافقة على شراء {$this->points} نقطة وإضافتها إلى حسابك.";
        } else {
            $title = 'Purchase Request Cancelled';
            $titleAr = 'تم إلغاء طلب الشراء';
            $body = "Your purchase request for {$this->points} points has been cancelled.";
            $bodyAr = "تم إلغاء طلب شراء {$this->points} نقطة.";
        }

        return FcmMessage::create()
            ->setData([
                'type' => 'point_purchase',
                'status' => $this->status,
                'points' => (string) $this->points,
                'request_id' => $this->requestId ?? '',
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body)
                ->setImage($logoImageUrl))
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('point_purchase_' . $this->status))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#4CAF50')
                            ->setSound('default')
                            ->setChannelId('points_channel')
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('point_purchase_' . $this->status))
                    ->setPayload([
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ]
                    ])
            );
    }
}
