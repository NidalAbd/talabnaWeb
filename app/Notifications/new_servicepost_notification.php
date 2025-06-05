<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class new_servicepost_notification extends Notification
{
    use Queueable;

    private mixed $servicePost;
    private mixed $user;
    private mixed $token_fcm;

    public function __construct($servicePost , $user,$fcm_token)
    {
        $this->servicePost = $servicePost;
        $this->user = $user;
        $this->token_fcm = $fcm_token;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $title = $this->servicePost->title;
        $type = ($this->servicePost->type === 'عرض') ? 'offer' : 'request';
        $body = "{$this->user->user_name} {$type} the following service";
        return FcmMessage::create()
            ->setData(['data1' => 'value', 'data2' => 'value2'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body))
            ->setToken($this->token_fcm)
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()->setColor('#0A0A0A'))
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Service Post')
            ->line('Hello,')
            ->line('A new service post is available in the ' . $this->servicePost->sub_category . ' category.')
            ->line('Posted by: ' . $this->user->user_name)
            ->action('View Service Post', url('/service-posts/' . $this->servicePost->id))
            ->line('Thank you for using our application!');
    }
}
