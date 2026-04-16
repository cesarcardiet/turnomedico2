<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionUpdate extends Notification
{
    use Queueable;

    private $status;
    private $message;

    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (config('mail.mailers.smtp.host') || config('mail.default') !== 'smtp') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $subject = $this->status === 'approved' ? '¡Suscripción Aprobada!' : ($this->status === 'pending' ? 'Notificación de Suscripción' : 'Suscripción Rechazada');

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line($this->message)
            ->action('Ver Membresía', url('/doctor/membership'))
            ->line('Gracias por usar nuestra plataforma.');
    }

    public function toArray($notifiable): array
    {
        $url = $this->status === 'pending' ? '/admin/subscriptions' : '/doctor/membership';

        return [
            'title' => $this->status === 'approved' ? 'Suscripción Aprobada' : ($this->status === 'pending' ? 'Nueva Suscripción' : 'Suscripción Rechazada'),
            'message' => $this->message,
            'status' => $this->status === 'approved' ? 'success' : ($this->status === 'pending' ? 'info' : 'error'),
            'type' => 'subscription',
            'action_url' => $url
        ];
    }
}
