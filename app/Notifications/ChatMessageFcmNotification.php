<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/**
 * Push for a new chat message. Sent right after the response (not via the minute-queue).
 *
 * Like WhatsApp (2026-10-07: 30 messages made 30 notifications):
 *  - nothing while the person has this chat open (the app polls it every few seconds, see markViewing);
 *  - one notification per conversation: each push replaces the last one (Android tag, iOS collapse id) and says
 *    "5 new messages" with the latest text;
 *  - only the first push of a burst makes a sound; the next ones within 30 seconds update quietly.
 */
class ChatMessageFcmNotification extends Notification
{
    private const VIEWING_SECONDS = 10;
    private const QUIET_SECONDS = 30;

    public function __construct(private int $conversationId, private string $senderName, private string $preview)
    {
    }

    public static function markViewing(int $conversationId, int $userId): void
    {
        Cache::put(self::viewingKey($conversationId, $userId), 1, now()->addSeconds(self::VIEWING_SECONDS));
    }

    private static function viewingKey(int $conversationId, int $userId): string
    {
        return "chat:viewing:{$conversationId}:{$userId}";
    }

    public function via($notifiable): array
    {
        if (empty($notifiable->fcm_token) || Cache::has(self::viewingKey($this->conversationId, (int) $notifiable->id))) {
            return [];
        }

        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $unread = Message::where('conversation_id', $this->conversationId)
            ->where('sender_id', '!=', $notifiable->id)
            ->whereNull('read_at')
            ->count();
        $preview = mb_strimwidth($this->preview, 0, 140, '…');
        $body = $unread > 1 ? self::countLine($unread, $notifiable).' · '.$preview : $preview;

        // A sound only for the first message of a burst.
        $quietKey = "chat:pushed:{$this->conversationId}:{$notifiable->id}";
        $quiet = Cache::has($quietKey);
        Cache::put($quietKey, 1, now()->addSeconds(self::QUIET_SECONDS));

        $tag = 'chat_'.$this->conversationId;
        $aps = ['thread-id' => $tag, 'badge' => max(1, $unread)];
        if (! $quiet) {
            $aps['sound'] = 'default';
        }

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
                    ->body($body)
            )
            ->custom([
                'android' => [
                    'collapse_key' => $tag,
                    'notification' => array_filter([
                        'tag' => $tag,
                        'notification_count' => $unread > 1 ? $unread : null,
                        'default_sound' => $quiet ? null : true,
                    ], fn ($v) => $v !== null),
                ],
                'apns' => [
                    'headers' => ['apns-collapse-id' => $tag],
                    'payload' => ['aps' => $aps],
                ],
            ]);
    }

    private static function countLine(int $count, $notifiable): string
    {
        $lang = (string) ($notifiable->locale ?? 'ar');

        return str_starts_with($lang, 'en') ? "{$count} new messages" : "{$count} رسائل جديدة";
    }
}
