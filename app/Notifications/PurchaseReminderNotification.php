<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/** "Finish your purchase?" — opens the buy-points screen. */
class PurchaseReminderNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    public $tries = 3;
    use \Illuminate\Bus\Queueable;

    public const TEXT = [
        'failed' => [
            'en' => ['Your purchase didn’t go through', 'No charge was made. Tap to try again.'],
            'ar' => ['لم تكتمل عملية الشراء', 'لم يتم خصم أي مبلغ. اضغط للمحاولة مرة أخرى.'],
        ],
        'cancelled' => [
            'en' => ['Still want points?', 'Your points are one tap away — pick up where you left off.'],
            'ar' => ['ما زلت تريد النقاط؟', 'نقاطك على بُعد نقرة — أكمل من حيث توقفت.'],
        ],
        'default' => [
            'en' => ['Finish your purchase?', 'Your points purchase wasn’t completed. Tap to continue.'],
            'ar' => ['أكمل عملية الشراء؟', 'لم تكتمل عملية شراء النقاط. اضغط للمتابعة.'],
        ],
    ];

    public function __construct(private string $status, private string $productId)
    {
    }

    public static function text(string $status, ?string $locale): array
    {
        $set = self::TEXT[$status] ?? self::TEXT['default'];
        return $set[$locale === 'ar' ? 'ar' : 'en'];
    }

    public function via($notifiable): array
    {
        return !empty($notifiable->fcm_token) ? [FcmChannel::class] : [];
    }

    public function toFcm($notifiable): FcmMessage
    {
        [$title, $body] = self::text($this->status, $notifiable->locale ?? 'ar');
        [$titleAr, $bodyAr] = self::text($this->status, 'ar');
        return FcmMessage::create()
            ->data([
                'type' => 'purchase_reminder',
                'product_id' => $this->productId,
                'target_type' => 'buy_points',
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(\NotificationChannels\Fcm\Resources\Notification::create()->title($title)->body($body));
    }
}
