<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/**
 * "Your AI video/image is ready" (2026-10-07). Long AI jobs go on while the user browses; the push brings them back
 * to the post form, where the result waits in "Your creations".
 */
class AiReadyNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Queueable;

    public $tries = 3;

    private const TEXT = [
        'video' => [
            'en' => ['Your AI video is ready', 'Tap to add it to your post.'],
            'ar' => ['فيديو الذكاء الاصطناعي جاهز', 'اضغط لإضافته إلى إعلانك.'],
        ],
        'image' => [
            'en' => ['Your AI photo is ready', 'Tap to add it to your post.'],
            'ar' => ['صورة الذكاء الاصطناعي جاهزة', 'اضغط لإضافتها إلى إعلانك.'],
        ],
    ];

    public function __construct(private string $kind, private string $requestUuid)
    {
    }

    public function via($notifiable): array
    {
        return empty($notifiable->fcm_token) ? [] : [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $kind = $this->kind === 'video' ? 'video' : 'image';
        $lang = ($notifiable->locale ?? 'ar') === 'ar' ? 'ar' : 'en';
        [$title, $body] = self::TEXT[$kind][$lang];

        return FcmMessage::create()
            ->data([
                'type' => 'ai_ready',
                'target_type' => 'ai_creation',
                'target_id' => '',
                'request_id' => $this->requestUuid,
                'title_ar' => self::TEXT[$kind]['ar'][0],
                'body_ar' => self::TEXT[$kind]['ar'][1],
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(\NotificationChannels\Fcm\Resources\Notification::create()->title($title)->body($body));
    }
}
