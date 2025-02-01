<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class email_changed extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Email address changed')
            ->line('Your email address has been changed to ' . $notifiable->email)
            ->line('If you did not initiate this change, please contact us immediately.')
            ->line('Thank you for using our application!');
    }

}
