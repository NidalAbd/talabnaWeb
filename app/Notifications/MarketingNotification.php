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

class MarketingNotification extends Notification
{
    use Queueable;

    private $title;
    private $body;
    private $imageUrl;
    private $deepLink;
    private $deviceToken;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $body, $imageUrl = null, $deepLink = null, $deviceToken)
    {
        $this->title = $title;
        $this->body = $body;
        $this->imageUrl = $imageUrl;
        $this->deepLink = $deepLink;
        $this->deviceToken = $deviceToken;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    /**
     * Get the FCM representation of the notification.
     *
     * @param mixed $notifiable
     * @return \NotificationChannels\Fcm\FcmMessage
     */
    public function toFcm($notifiable)
    {
        $data = [
            'type' => 'marketing',
            'title' => $this->title,
            'body' => $this->body,
        ];

        if ($this->imageUrl) {
            $data['image_url'] = $this->imageUrl;
        }

        // Transform any direct scheme links to web URL format
        $deepLink = $this->deepLink;
        if ($deepLink) {
            // If it's already a https link, keep it as is
            if (!str_starts_with($deepLink, 'https://')) {
                // Convert talabna://reels/1026 to https://talbna.cloud/api/deep-link/reels/1026
                $deepLink = preg_replace('/^talabna:\/\//', 'https://talbna.cloud/api/deep-link/', $deepLink);
            }
            $data['deep_link'] = $deepLink;
        }

        // Add app logo
        $logoImageUrl = 'https://talbna.cloud/img/logo.png';
        $data['logo_url'] = $logoImageUrl;

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->body)
                ->setImage($this->imageUrl))
            ->setToken($this->deviceToken)
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('marketing_campaign'))
                    ->setNotification(AndroidNotification::create()
                        ->setClickAction($deepLink) // Use the converted URL
                        ->setColor('#FE7A1A')
                        ->setIcon('@mipmap/ic_launcher'))
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('marketing_campaign_ios'))
            );
    }
}
