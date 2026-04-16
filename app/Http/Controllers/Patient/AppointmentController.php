<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['doctorProfile.user', 'doctorProfile.speciality', 'timeSlot'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Patient/Appointments', [
            'appointments' => $appointments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_profile_id' => 'required|exists:doctor_profiles,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'problem_description' => 'required|string|max:1000',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        $slot = TimeSlot::findOrFail($validated['time_slot_id']);

        if ($slot->is_booked) {
            return redirect()->back()->withErrors(['time_slot_id' => 'Este horario ya ha sido reservado por otro paciente.']);
        }

        $paymentProof = $request->file('payment_proof');
        $proofPath = $paymentProof ? $paymentProof->store('appointment_proofs', 'public') : null;
        $paymentStatus = $proofPath ? 'pending' : 'not_required';
        $paymentMethod = $proofPath ? 'manual' : null;

        $appointment = Appointment::create([
            'user_id' => auth()->id(),
            'doctor_profile_id' => $validated['doctor_profile_id'],
            'time_slot_id' => $validated['time_slot_id'],
            'problem_description' => $validated['problem_description'],
            'status' => 'pending',
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'payment_proof' => $proofPath,
        ]);

        $slot->update(['is_booked' => true]);

        // Cargar relaciones necesarias
        $appointment->load(['doctorProfile.user', 'timeSlot']);

        // Calcular número de turno
        $turnNumber = $appointment->turn_number;

        // Notify Doctor
        $appointment->doctorProfile->user->notify(new \App\Notifications\AppointmentBooked($appointment));

        return redirect()->route('patient.appointments.index')->with([
            'appointment' => [
                'id' => $appointment->id,
                'turn_number' => $turnNumber,
                'doctor_name' => $appointment->doctorProfile->user->name,
                'date' => $appointment->timeSlot->date,
                'time' => substr($appointment->timeSlot->start_time, 0, 5),
                'status' => 'pending',
                'payment_status' => 'pending'
            ]
        ]);
    }
}
