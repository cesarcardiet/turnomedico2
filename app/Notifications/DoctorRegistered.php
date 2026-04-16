<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DoctorRegistered extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nuevo Médico Registrado: ' . $this->user->name)
            ->line('Un nuevo profesional se ha unido a la plataforma y espera aprobación.')
            ->action('Ver Listado de Médicos', url('/admin/doctors'))
            ->line('Por favor, revisa su perfil para habilitar su cuenta.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'doctor_registered',
            'title' => 'Nuevo Registro Médico',
            'message' => 'Nuevo médico registrado: ' . $this->user->name . '. Requiere aprobación.',
            'status' => 'info',
            'action_url' => '/admin/doctors',
        ];
    }
}
