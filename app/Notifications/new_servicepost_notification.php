<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;

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
        $title = $this->servicePost->translate('title') ?? 'New Post';
        $type = ($this->servicePost->type === 'عرض') ? 'offer' : 'request';
        $body = "{$this->user->user_name} {$type} the following service";
        return FcmMessage::create()
            ->data([
                'type' => 'new_service_post',
                'post_id' => (string) $this->servicePost->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ])
            ->notification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($title)
                    ->body($body)
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
