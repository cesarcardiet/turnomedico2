<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * Display the public Queue Monitor for a specific doctor.
     * Intended for TV screens in waiting rooms.
     */
    public function show($doctorId)
    {
        // Fix: Use profile_photo_url instead of profile_photo_path
        $doctor = DoctorProfile::with(['user', 'speciality'])->findOrFail($doctorId);

        $appointments = Appointment::with(['user', 'timeSlot'])
            ->where('doctor_profile_id', $doctor->id)
            ->whereHas('timeSlot', function ($query) {
                $query->where('date', Carbon::today()->toDateString());
            })
            ->whereIn('status', ['accepted', 'completed', 'in_consultation'])
            ->get();

        return Inertia::render('Doctor/PublicQueue', [
            'doctor' => $doctor,
            'appointments' => $appointments,
            'today' => Carbon::today()->format('d/m/Y')
        ]);
    }

    /**
     * API endpoint for real-time updates of the queue.
     */
    public function getQueueData($id)
    {
        $appointments = Appointment::with(['user', 'timeSlot'])
            ->where('doctor_profile_id', $id)
            ->whereHas('timeSlot', function ($query) {
                $query->where('date', Carbon::today()->toDateString());
            })
            ->whereIn('status', ['accepted', 'completed', 'in_consultation'])
            ->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }
}
