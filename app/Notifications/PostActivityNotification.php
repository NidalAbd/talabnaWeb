<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

/**
 * Pushes about posts (Release A, 2026-10-07), in the user's app language (users.locale):
 *  - expiring:     your post ends in a few days, renew it
 *  - expired:      your post ended, renew it in one tap
 *  - renewed:      Pro/Business: your post was renewed automatically
 *  - price_drop:   a post you saved is now cheaper
 *  - saved_search: new posts match one of your saved searches
 * Tapping opens the post (or the search results for saved_search).
 */
class PostActivityNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Queueable;

    public $tries = 3;

    private const TEXT = [
        'expiring' => [
            'en' => ['Your post ends soon', '"{title}" ends in {days} days. Renew it to keep it visible.'],
            'ar' => ['ينتهي إعلانك قريبًا', 'ينتهي "{title}" خلال {days} أيام. جدّده ليبقى ظاهرًا.'],
        ],
        'expired' => [
            'en' => ['Your post has ended', '"{title}" is no longer shown. Renew it in one tap.'],
            'ar' => ['انتهى إعلانك', 'لم يعد "{title}" ظاهرًا. جدّده بلمسة واحدة.'],
        ],
        'renewed' => [
            'en' => ['Post renewed', '"{title}" was renewed automatically with your plan.'],
            'ar' => ['تم تجديد الإعلان', 'تم تجديد "{title}" تلقائيًا مع خطتك.'],
        ],
        'price_drop' => [
            'en' => ['Price dropped', '"{title}" is now {price}.'],
            'ar' => ['انخفض السعر', 'أصبح سعر "{title}" الآن {price}.'],
        ],
        'saved_search' => [
            'en' => ['New posts for your search', '{count} new posts match "{query}".'],
            'ar' => ['إعلانات جديدة لبحثك', '{count} إعلانات جديدة تطابق "{query}".'],
        ],
    ];

    /** @param array<string,string> $params */
    public function __construct(private string $kind, private int $targetId, private array $params = [])
    {
    }

    public function via($notifiable): array
    {
        return empty($notifiable->fcm_token) ? [] : [FcmChannel::class];
    }

    /** @return array{0:string,1:string} [title, body] in the user's language */
    public static function render(string $kind, string $locale, array $params): array
    {
        $lang = $locale === 'ar' ? 'ar' : 'en';
        [$title, $body] = self::TEXT[$kind][$lang];
        $replace = [];
        foreach ($params as $k => $v) {
            $replace['{'.$k.'}'] = $v;
        }

        return [strtr($title, $replace), strtr($body, $replace)];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $locale = $notifiable->locale ?? 'ar';
        [$title, $body] = self::render($this->kind, $locale, $this->params);
        [$titleAr, $bodyAr] = self::render($this->kind, 'ar', $this->params);
        $isSearch = $this->kind === 'saved_search';

        return FcmMessage::create()
            ->data([
                'type' => 'post_'.$this->kind,
                'target_type' => $isSearch ? 'saved_search' : 'post',
                'target_id' => (string) $this->targetId,
                'post_id' => $isSearch ? '' : (string) $this->targetId,
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(\NotificationChannels\Fcm\Resources\Notification::create()->title($title)->body($body));
    }
}
