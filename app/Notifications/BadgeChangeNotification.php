<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

class BadgeChangeNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    public $tries = 3;
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

    /**
     * The push is shown in the user's app language (users.locale), like the other pushes. Callers pass the Arabic
     * badge name; it used to go into the English sentence as is ("...set to ماسي.", 2026-10-06), so both names are
     * resolved here.
     */
    public function toFcm($notifiable): FcmMessage
    {
        [$nameEn, $nameAr] = $this->names();
        $ar = ($notifiable->locale ?? 'ar') === 'ar';

        switch ($this->type) {
            case 'applied':
                $title = 'Badge Activated';
                $titleAr = 'تم تفعيل الشارة';
                $body = "Your post badge has been set to {$nameEn}.";
                $bodyAr = "تم تفعيل شارة {$nameAr} على إعلانك.";
                break;
            case 'upgraded':
                $title = 'Badge Upgraded';
                $titleAr = 'تمت ترقية الشارة';
                $body = "Your post badge has been upgraded to {$nameEn}.";
                $bodyAr = "تمت ترقية شارة إعلانك إلى {$nameAr}.";
                break;
            case 'switched':
                $title = 'Badge Changed';
                $titleAr = 'تم تغيير الشارة';
                $body = "Your post badge has been changed to {$nameEn}.";
                $bodyAr = "تم تغيير شارة إعلانك إلى {$nameAr}.";
                break;
            case 'expired':
                $title = 'Badge Expired';
                $titleAr = 'انتهت صلاحية الشارة';
                $body = $nameEn === '' ? 'Your post badge has expired and changed to normal.' : "Your {$nameEn} badge has expired and changed to normal.";
                $bodyAr = 'انتهت صلاحية الشارة على إعلانك وعاد إعلانًا عاديًا.';
                break;
            default:
                $title = 'Badge Update';
                $titleAr = 'تحديث الشارة';
                $body = 'Your post badge has been updated.';
                $bodyAr = 'تم تحديث شارة إعلانك.';
        }
        $bodyAr = $this->messageAr ?: $bodyAr;

        return FcmMessage::create()
            ->data([
                'type' => 'badge_' . $this->type,
                'post_id' => (string) $this->postId,
                'target_type' => 'post',
                'target_id' => (string) $this->postId,
                'badge_name' => $ar ? $nameAr : $nameEn,
                'title_ar' => $titleAr,
                'body_ar' => $bodyAr,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($ar ? $titleAr : $title)
                    ->body($ar ? $bodyAr : $body)
            );
    }

    /** [English, Arabic] badge name from whatever name the caller passed (usually the Arabic one). */
    private function names(): array
    {
        $given = trim($this->badgeName);
        if ($given === '' || $given === 'الشارة') {
            return ['', 'الشارة'];
        }
        try {
            $badge = \App\Models\BadgeType::all()->first(fn ($b) => in_array($given, [$b->name_ar, $b->name_en], true));
            if ($badge) {
                return [$badge->name_en, $badge->name_ar];
            }
        } catch (\Throwable $e) {
            // fall through to the built-in names
        }
        $known = ['ذهبي' => 'Gold', 'ماسي' => 'Diamond', 'فضي' => 'Silver', 'برونزي' => 'Bronze', 'عادي' => 'Normal'];

        return isset($known[$given]) ? [$known[$given], $given] : [$given, $given];
    }
}
