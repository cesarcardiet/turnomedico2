<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentBooked extends Notification
{
    use Queueable;

    protected $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nueva Cita Agendada')
            ->line('Se ha agendado una nueva cita para el ' . $this->appointment->timeSlot->date . ' a las ' . $this->appointment->timeSlot->start_time)
            ->action('Ver Citas', url('/doctor/appointments'))
            ->line('¡Gracias por usar Turno Médico!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'appointment_booked',
            'title' => 'Nueva Cita Recibida',
            'message' => 'Nueva cita de ' . $this->appointment->user->name,
            'appointment_id' => $this->appointment->id,
            'action_url' => '/doctor/appointments',
        ];
    }
}
