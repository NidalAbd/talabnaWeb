<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class point_recived_notifications extends Notification
{
    use Queueable;
    protected $sender;
    protected $receiver;
    protected $points;
    protected $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($sender, $receiver, $points, $subject)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->points = $points;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }


    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received ' . $this->points . ' points from ' . $this->sender->user_name)
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using our application!');
    }
}
