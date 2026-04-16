<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'general_notification',
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => '/dashboard',
        ];
    }
}
