<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

class BadgeChangeNotification extends Notification
{
    use Queueable;

    private string $type; // applied, upgraded, switched, expired
    private string $badgeName;
    private int $postId;
    private string $messageAr;

    public function __construct(string $type, string $badgeName, int $postId, string $messageAr = '')
    {
        $this->type = $type;
        $this->badgeName = $badgeName;
        $this->postId = $postId;
        $this->messageAr = $messageAr;
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
        switch ($this->type) {
            case 'applied':
                $title = 'Badge Activated';
                $titleAr = 'تم تفعيل الشارة';
                $body = "Your post badge has been set to {$this->badgeName}.";
                break;
            case 'upgraded':
                $title = 'Badge Upgraded';
                $titleAr = 'تمت ترقية الشارة';
                $body = "Your post badge has been upgraded to {$this->badgeName}.";
                break;
            case 'switched':
                $title = 'Badge Changed';
                $titleAr = 'تم تغيير الشارة';
                $body = "Your post badge has been changed to {$this->badgeName}.";
                break;
            case 'expired':
                $title = 'Badge Expired';
                $titleAr = 'انتهت صلاحية الشارة';
                $body = "Your {$this->badgeName} badge has expired and changed to normal.";
                break;
            default:
                $title = 'Badge Update';
                $titleAr = 'تحديث الشارة';
                $body = "Your post badge has been updated.";
        }

        return FcmMessage::create()
            ->data([
                'type' => 'badge_' . $this->type,
                'post_id' => (string) $this->postId,
                'badge_name' => $this->badgeName,
                'title_ar' => $titleAr,
                'body_ar' => $this->messageAr ?: $body,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($title)
                    ->body($body)
            );
    }
}
