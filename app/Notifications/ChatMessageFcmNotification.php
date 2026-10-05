<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/** Push for a new chat message. Sent right after the response (not via the minute-queue). */
class ChatMessageFcmNotification extends Notification
{
    public function __construct(private int $conversationId, private string $senderName, private string $preview)
    {
    }

    public function via($notifiable): array
    {
        return ! empty($notifiable->fcm_token) ? [FcmChannel::class] : [];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $preview = mb_strimwidth($this->preview, 0, 140, '…');

        return FcmMessage::create()
            ->data([
                'type' => 'chat_message',
                'target_type' => 'conversation',
                'target_id' => (string) $this->conversationId,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($this->senderName)
                    ->body($preview)
            );
    }
}
