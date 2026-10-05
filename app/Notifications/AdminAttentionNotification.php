<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

/** Tells admins that charged actions or payments went wrong and need a look (see admin:alerts). */
class AdminAttentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<string,int> $counts what is new, e.g. ['ai_refund_failed' => 1] */
    public function __construct(private array $counts, private string $summaryEn, private string $summaryAr)
    {
    }

    public function via($notifiable): array
    {
        return empty($notifiable->fcm_token) ? ['database'] : ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return ['message' => $this->summaryEn, 'message_ar' => $this->summaryAr, 'type' => 'admin_attention', 'counts' => $this->counts];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $ar = ($notifiable->locale ?? 'ar') === 'ar';

        return FcmMessage::create()
            ->data(['type' => 'admin_attention', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'body_ar' => $this->summaryAr])
            ->notification(FcmNotification::create()
                ->title($ar ? 'تنبيه للمشرف: عمليات تحتاج مراجعة' : 'Admin: payments or AI actions need a look')
                ->body($ar ? $this->summaryAr : $this->summaryEn));
    }
}
