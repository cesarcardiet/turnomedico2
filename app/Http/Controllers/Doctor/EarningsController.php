<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EarningsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $doctorProfileId = $user->doctorProfile->id;

        // Ingresos Reales (Citas completadas y pagadas)
        $totalEarnings = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->where('payment_status', 'approved')
            ->where('status', 'completed')
            ->join('doctor_profiles', 'appointments.doctor_profile_id', '=', 'doctor_profiles.id')
            ->sum('doctor_profiles.consultation_fee');

        // Ingresos Proyectados (Citas aceptadas pero no completadas aún)
        $projectedEarnings = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->where('payment_status', 'approved')
            ->where('status', 'accepted')
            ->join('doctor_profiles', 'appointments.doctor_profile_id', '=', 'doctor_profiles.id')
            ->sum('doctor_profiles.consultation_fee');

        $appointments = Appointment::with(['user', 'timeSlot'])
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('payment_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Doctor/Earnings', [
            'totalEarnings' => (float) $totalEarnings,
            'projectedEarnings' => (float) $projectedEarnings,
            'transactions' => $appointments
        ]);
    }
}
