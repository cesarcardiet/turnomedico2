<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentStatusUpdated extends Notification
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
        $statusLabels = [
            'accepted' => 'Aceptada',
            'rejected' => 'Rechazada',
            'completed' => 'Completada',
            'absent' => 'Ausente',
            'in_consultation' => 'En Consulta'
        ];
        $status = $statusLabels[$this->appointment->status] ?? $this->appointment->status;

        return (new MailMessage)
            ->subject('Actualización de Cita: ' . $status)
            ->greeting('Hola ' . $notifiable->name . '!')
            ->line('El estado de tu cita con el Dr. ' . $this->appointment->doctorProfile->user->name . ' ha sido actualizado a: ' . $status)
            ->line('Fecha: ' . $this->appointment->timeSlot->date)
            ->line('Horario: ' . substr($this->appointment->timeSlot->start_time, 0, 5))
            ->action('Ver mi Panel', url('/patient/dashboard'))
            ->line('¡Gracias por confiar en nosotros!');
    }

    public function toArray($notifiable)
    {
        $statusLabels = [
            'accepted' => 'Aceptada',
            'rejected' => 'Rechazada',
            'completed' => 'Completada',
            'absent' => 'Ausente',
            'in_consultation' => 'En Consulta'
        ];
        $status = $statusLabels[$this->appointment->status] ?? $this->appointment->status;

        $paymentInfo = '';
        if ($this->appointment->payment_status === 'verified' || $this->appointment->payment_status === 'approved') {
            $paymentInfo = ' y tu pago ha sido aprobado';
        } elseif ($this->appointment->payment_status === 'rejected') {
            $paymentInfo = ' (tu pago fue rechazado)';
        }
        // payment_status 'not_required' = turno sin pago, no se menciona pago

        return [
            'type' => 'appointment_status_updated',
            'title' => 'Actualización de Cita',
            'message' => 'Tu cita del ' . $this->appointment->timeSlot->date . ' está ahora ' . $status . $paymentInfo,
            'appointment_id' => $this->appointment->id,
            'action_url' => '/patient/dashboard',
        ];
    }
}
