<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DoctorApproved extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        // Only send mail if MAIL_MAILER is configured (common issue in cPanel)
        if (config('mail.mailers.smtp.host') || config('mail.default') !== 'smtp') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('¡Perfil Aprobado en Turno Médico!')
            ->greeting('Hola, ' . $notifiable->name . '!')
            ->line('Tu perfil profesional ha sido verificado y aprobado por nuestro equipo.')
            ->line('Ya puedes configurar tu horario y comenzar a recibir citas de pacientes.')
            ->action('Ir al Tablero Médico', url('/doctor/dashboard'))
            ->line('¡Gracias por unirte a nuestra red de especialistas!')
            ->salutation('Atentamente, El equipo de Turno Médico');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'doctor_approved',
            'title' => '¡Perfil Aprobado!',
            'message' => '¡Felicidades! Tu perfil ha sido aprobado. Ya puedes configurar tu agenda.',
            'status' => 'success',
            'action_url' => '/doctor/dashboard',
        ];
    }
}
