<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $doctorProfile = auth()->user()->doctorProfile;

        $appointments = Appointment::with(['user', 'timeSlot'])
            ->where('appointments.doctor_profile_id', $doctorProfile->id)
            ->join('time_slots', 'appointments.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.date', 'asc')
            ->orderBy('time_slots.start_time', 'asc')
            ->select('appointments.*')
            ->get();

        return Inertia::render('Doctor/Appointments', [
            'appointments' => $appointments,
            'server_today' => now()->toDateString(),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected,completed,absent,in_consultation',
        ]);

        $authUser = auth()->user();
        $authProfile = $authUser?->doctorProfile;

        \Log::info('Doctor intenta cambiar estado de cita', [
            'appointment_id' => $id,
            'requested_status' => $request->input('status'),
            'auth_user_id' => $authUser?->id,
            'auth_doctor_profile_id' => $authProfile->id ?? null,
        ]);

        $appointment = Appointment::findOrFail($id);

        // Security check con log (comparando IDs como enteros, no por tipo estricto)
        if (!$authProfile || (int) $appointment->doctor_profile_id !== (int) $authProfile->id) {
            \Log::warning('Intento de cambiar estado de cita que no pertenece al doctor autenticado', [
                'appointment_id' => $appointment->id,
                'appointment_doctor_profile_id' => $appointment->doctor_profile_id,
                'auth_user_id' => $authUser?->id,
                'auth_doctor_profile_id' => $authProfile->id ?? null,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'No puedes modificar una cita que no pertenece a tu agenda.',
            ]);
        }

        $appointment->update(['status' => $validated['status']]);

        \Log::info('Estado de cita actualizado', [
            'appointment_id' => $appointment->id,
            'new_status' => $appointment->status,
        ]);

        // If rejected, free up the slot
        if ($validated['status'] === 'rejected') {
            $appointment->timeSlot->update(['is_booked' => false]);
        }

        // Cargar relaciones necesarias para la notificación
        $appointment->load(['user', 'timeSlot', 'doctorProfile.user']);

        // Notify Patient
        try {
            $appointment->user->notify(new \App\Notifications\AppointmentStatusUpdated($appointment));
            \Log::info("Notificación enviada al paciente {$appointment->user->email} por cambio a estado: {$validated['status']}");
        } catch (\Exception $e) {
            \Log::error("Error enviando notificación al paciente: " . $e->getMessage());
        }

        return redirect()->back()->with('message', 'Appointment status updated to ' . $validated['status']);
    }
}
