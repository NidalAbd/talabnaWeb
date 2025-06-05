<?php /** @noinspection ALL */

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

class point_purchase_notifications extends Notification
{
    use Queueable;

    private $points;
    private $subject;
    private $device_token;

    public function __construct($points, $subject, $device_token)
    {
        $this->points = $points;
        $this->subject = $subject;
        $this->device_token = $device_token;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $title = $this->subject;
        $body = 'You have purchased ' . $this->points . ' points and approved successfully';
        $logoImageUrl = 'https://talbna.cloud/img/logo.png';

        return FcmMessage::create()
            ->setData([
                'data1' => 'value',
                'data2' => 'value2',
                'logoImageUrl' => $logoImageUrl,
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body))
            ->setToken($this->device_token)
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create())
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello ' . $notifiable->user_name . ',')
            ->line('You have purchased ' . $this->points . ' points and approved successfully')
            ->line('Thank you for using our application!');
    }
}

