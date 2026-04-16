<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function dashboard(Request $request)
    {
        $doctor = $request->user()->doctorProfile;

        if (!$doctor) {
            return response()->json(['message' => 'Perfil de doctor no encontrado.'], 404);
        }

        $stats = [
            'total_appointments' => $doctor->appointments()->count(),
            'pending_appointments' => $doctor->appointments()->where('status', 'pending')->count(),
            'today_appointments' => $doctor->appointments()->whereDate('appointment_date', today())->count(),
        ];

        $recentAppointments = $doctor->appointments()
            ->with('user')
            ->orderBy('appointment_date', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_appointments' => $recentAppointments,
        ]);
    }

    public function appointments(Request $request)
    {
        $doctor = $request->user()->doctorProfile;

        $appointments = $doctor->appointments()
            ->with('user')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json($appointments);
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed,cancelled',
        ]);

        $doctor = $request->user()->doctorProfile;
        $appointment = $doctor->appointments()->findOrFail($id);

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Estado de la cita actualizado.',
            'appointment' => $appointment
        ]);
    }
}
