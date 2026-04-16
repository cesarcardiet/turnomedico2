<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HolidayController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->doctorProfile) {
            return redirect()->route('doctor.membership.index')->with('error', 'Debes completar tu perfil primero.');
        }

        $holidays = Holiday::where('doctor_profile_id', $user->doctorProfile->id)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return Inertia::render('Doctor/Holidays', [
            'holidays' => $holidays
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255'
        ]);

        $doctorProfileId = auth()->user()->doctorProfile->id;

        // Check if already exists
        $exists = Holiday::where('doctor_profile_id', $doctorProfileId)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['date' => 'Ya has marcado este día como vacaciones.']);
        }

        Holiday::create([
            'doctor_profile_id' => $doctorProfileId,
            'date' => $request->date,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('message', 'Día de vacaciones agregado.');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);

        $authUser = auth()->user();
        $authProfile = $authUser?->doctorProfile;

        // Ensure it belongs to the doctor (comparando IDs como enteros)
        if (!$authProfile || (int) $holiday->doctor_profile_id !== (int) $authProfile->id) {
            \Log::warning('Intento de eliminar día de vacaciones que no pertenece al doctor autenticado', [
                'holiday_id' => $holiday->id,
                'holiday_doctor_profile_id' => $holiday->doctor_profile_id,
                'auth_user_id' => $authUser?->id,
                'auth_doctor_profile_id' => $authProfile->id ?? null,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'No puedes eliminar un día de vacaciones que no pertenece a tu agenda.',
            ]);
        }

        $holiday->delete();

        return redirect()->back()->with('message', 'Día de vacaciones eliminado.');
    }
}
