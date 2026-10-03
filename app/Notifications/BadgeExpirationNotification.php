<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

/**
 * "Your badge expired" push, in the user's app language (users.locale),
 * with the badge names translated too.
 */
class BadgeExpirationNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    public $tries = 3;
    use Queueable;

    /** [title, body] — {badge}, {normal}, {post} are filled in. */
    public const TEXT = [
        'en' => ['Badge expired', 'Your {badge} badge on post #{post} has expired and is now {normal}.'],
        'ar' => ['انتهت صلاحية الشارة', 'انتهت صلاحية شارة {badge} على منشورك #{post} وأصبحت {normal}.'],
        'fr' => ['Badge expiré', 'Votre badge {badge} sur l’annonce n°{post} a expiré et est maintenant {normal}.'],
        'es' => ['Insignia caducada', 'Tu insignia {badge} en la publicación #{post} ha caducado y ahora es {normal}.'],
        'pt' => ['Selo expirado', 'Seu selo {badge} na publicação #{post} expirou e agora é {normal}.'],
        'de' => ['Abzeichen abgelaufen', 'Dein Abzeichen „{badge}“ für Beitrag #{post} ist abgelaufen und jetzt „{normal}“.'],
        'tr' => ['Rozetin süresi doldu', '#{post} numaralı gönderindeki {badge} rozetinin süresi doldu, artık {normal}.'],
        'ru' => ['Срок значка истёк', 'Срок значка «{badge}» у публикации №{post} истёк, теперь он «{normal}».'],
        'id' => ['Lencana kedaluwarsa', 'Lencana {badge} pada postingan #{post} telah kedaluwarsa dan kini {normal}.'],
        'ms' => ['Lencana tamat tempoh', 'Lencana {badge} pada siaran #{post} telah tamat tempoh dan kini {normal}.'],
        'hi' => ['बैज की अवधि समाप्त', 'पोस्ट #{post} पर आपके {badge} बैज की अवधि समाप्त हो गई है, अब यह {normal} है।'],
        'ur' => ['بیج کی مدت ختم', 'پوسٹ #{post} پر آپ کے {badge} بیج کی مدت ختم ہو گئی ہے، اب یہ {normal} ہے۔'],
        'bn' => ['ব্যাজের মেয়াদ শেষ', 'পোস্ট #{post}-এ আপনার {badge} ব্যাজের মেয়াদ শেষ হয়েছে, এখন এটি {normal}।'],
        'zh' => ['徽章已过期', '您在帖子 #{post} 上的「{badge}」徽章已过期，现为「{normal}」。'],
        'fa' => ['مدت نشان تمام شد', 'مدت نشان {badge} در آگهی #{post} شما تمام شد و اکنون {normal} است.'],
        'ku' => ['ماوەی نیشانەکە تەواو بوو', 'ماوەی نیشانەی {badge} لە پۆستی #{post} تەواو بوو و ئێستا {normal}ە.'],
        'sw' => ['Muda wa beji umeisha', 'Muda wa beji yako ya {badge} kwenye chapisho #{post} umeisha, sasa ni {normal}.'],
    ];

    /**
     * @param array<string,string>|string $oldBadgeName  name per language (or one legacy name)
     * @param array<string,string>|string $normalName    the default badge's name per language
     */
    public function __construct(private array|string $oldBadgeName, private int $postId, private array|string $normalName = [])
    {
    }

    public function via($notifiable): array
    {
        return !empty($notifiable->fcm_token) ? [FcmChannel::class] : [];
    }

    public static function render(string $locale, array|string $badge, array|string $normal, int $postId): array
    {
        $lang = isset(self::TEXT[$locale]) ? $locale : 'en';
        $pick = fn ($v, string $fallback) => is_array($v) ? ($v[$lang] ?? $v['en'] ?? reset($v) ?: $fallback) : ($v ?: $fallback);
        [$title, $body] = self::TEXT[$lang];
        $body = strtr($body, [
            '{badge}' => $pick($badge, ''),
            '{normal}' => $pick($normal, $lang === 'ar' ? 'عادي' : 'Normal'),
            '{post}' => (string) $postId,
        ]);
        return [$title, $body];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $locale = (string) ($notifiable->locale ?? 'ar');
        [$title, $body] = self::render($locale, $this->oldBadgeName, $this->normalName, $this->postId);
        [$titleAr, $bodyAr] = self::render('ar', $this->oldBadgeName, $this->normalName, $this->postId);

        return FcmMessage::create()
            ->data([
                'type' => 'badge_expiration',
                'post_id' => (string) $this->postId,
                'target_type' => 'post',
                'target_id' => (string) $this->postId,
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
