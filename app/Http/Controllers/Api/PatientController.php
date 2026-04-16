<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use App\Models\Appointment;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function search(Request $request)
    {
        $query = DoctorProfile::with(['user', 'speciality'])
            ->where('is_approved', true)
            ->where('is_active', true);

        if ($request->has('speciality_id')) {
            $query->where('speciality_id', $request->speciality_id);
        }

        if ($request->has('q')) {
            $search = $request->q;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(10));
    }

    public function specialities()
    {
        return response()->json(Speciality::all());
    }

    public function doctorDetail($id)
    {
        $doctor = DoctorProfile::with([
            'user',
            'speciality',
            'reviews.user',
            'timeSlots' => function ($q) {
                $q->where('is_available', true);
            }
        ])->findOrFail($id);

        return response()->json($doctor);
    }

    public function appointments(Request $request)
    {
        $appointments = $request->user()->appointments()
            ->with(['doctorProfile.user', 'doctorProfile.speciality'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($appointments);
    }

    public function book(Request $request)
    {
        $request->validate([
            'doctor_profile_id' => 'required|exists:doctor_profiles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            'user_id' => $request->user()->id,
            'doctor_profile_id' => $request->doctor_profile_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $request->start_time,
            'status' => 'pending',
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Cita solicitada correctamente.',
            'appointment' => $appointment
        ], 201);
    }
}
