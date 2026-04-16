<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.edit')->with('error', 'Por favor completa tu perfil para continuar.');
        }

        $stats = [
            'total_appointments' => Appointment::where('doctor_profile_id', $doctorProfile->id)->count(),
            'total_reviews' => $user->reviews()->count(), // Assuming relationship exists or will be added
            'new_appointments' => Appointment::where('doctor_profile_id', $doctorProfile->id)->where('status', 'pending')->count(),
        ];

        $appointments = Appointment::with(['user', 'timeSlot'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->latest()
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'user' => $appointment->user,
                    'time_slot' => $appointment->timeSlot,
                    'status' => $appointment->status,
                    'problem_description' => $appointment->problem_description,
                    'payment_status' => $appointment->payment_status,
                    'payment_proof' => $appointment->payment_proof ? asset('storage/' . $appointment->payment_proof) : null,
                ];
            });

        return Inertia::render('Doctor/Dashboard', [
            'stats' => $stats,
            'appointments' => $appointments
        ]);
    }
}
