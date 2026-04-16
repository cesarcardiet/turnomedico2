<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        $doctorProfileId = auth()->user()->doctorProfile->id;

        $pendingPayments = Appointment::with(['user', 'timeSlot'])
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_proof')
            ->latest()
            ->get()
            ->map(function ($apt) {
                $apt->payment_proof_url = $apt->payment_proof ? asset('storage/' . $apt->payment_proof) : null;
                return $apt;
            });

        return Inertia::render('Doctor/Payments', [
            'pendingPayments' => $pendingPayments
        ]);
    }

    public function approve(Request $request, $id)
    {
        // Solo permitir aprobar citas que pertenezcan al doctor autenticado
        $appointment = Appointment::where('id', $id)
            ->where('doctor_profile_id', auth()->user()->doctorProfile->id)
            ->firstOrFail();

        $appointment->update([
            'payment_status' => 'verified',
            'status' => 'accepted' // Automatically accept appointment when payment is approved
        ]);

        // Cargar relaciones necesarias para la notificación
        $appointment->load(['user', 'timeSlot', 'doctorProfile.user']);

        // Notify Patient
        try {
            $appointment->user->notify(new \App\Notifications\AppointmentStatusUpdated($appointment));
            \Log::info("Notificación de PAGO APROBADO enviada al paciente {$appointment->user->email}");
        } catch (\Exception $e) {
            \Log::error("Error enviando notificación de pago: " . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Pago aprobado y cita aceptada.');
    }

    public function reject(Request $request, $id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('doctor_profile_id', auth()->user()->doctorProfile->id)
            ->firstOrFail();

        $appointment->update([
            'payment_status' => 'rejected',
            'status' => 'rejected'
        ]);

        // Free up slot
        $appointment->timeSlot->update(['is_booked' => false]);

        // Cargar relaciones necesarias para la notificación
        $appointment->load(['user', 'timeSlot', 'doctorProfile.user']);

        // Notify Patient about rejected payment
        try {
            $appointment->user->notify(new \App\Notifications\AppointmentStatusUpdated($appointment));
            \Log::info("Notificación de PAGO RECHAZADO enviada al paciente {$appointment->user->email}");
        } catch (\Exception $e) {
            \Log::error("Error enviando notificación de rechazo de pago: " . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Pago rechazado y turno liberado.');
    }
}
