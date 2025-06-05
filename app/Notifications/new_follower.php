<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class new_follower extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The follower instance.
     *
     * @var \App\Models\User
     */
    protected $follower;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\User $follower
     * @return void
     */
    public function __construct($follower)
    {
        $this->follower = $follower;
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Follower')
            ->line('You have a new follower on your profile.')
            ->line('Follower Details:')
            ->line('Name: ' . $this->follower->user_name)
            ->line('Email: ' . $this->follower->email)
            ->action('View Profile', url('/profile'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'You have a new follower on your profile.',
            'follower_name' => $this->follower->name,
            'follower_email' => $this->follower->email,
            'profile_url' => url('/profile'),
        ];
    }
}
