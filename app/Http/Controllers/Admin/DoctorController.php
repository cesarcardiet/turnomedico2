<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = DoctorProfile::with('user', 'speciality')->latest()->get()->map(function ($doctor) {
            $doctor->is_profile_complete = $doctor->isComplete();
            return $doctor;
        });
        return Inertia::render('Admin/Doctors', [
            'doctors' => $doctors,
            'specialities' => Speciality::all(),
        ]);
    }

    public function showProfile($id)
    {
        $doctor = DoctorProfile::with('user', 'speciality')->findOrFail($id);
        return Inertia::render('Admin/DoctorCurriculum', [
            'doctor' => $doctor,
        ]);
    }

    public function approve($id)
    {
        try {
            $doctor = DoctorProfile::findOrFail($id);
            $doctor->update(['is_approved' => true, 'is_active' => true]);

            try {
                // Notify Doctor
                $doctor->user->notify(new \App\Notifications\DoctorApproved());
            } catch (\Exception $e) {
                \Log::error('Error enviando notificación de aprobación médica: ' . $e->getMessage());
            }

            return redirect()->back()->with('message', 'Médico aprobado con éxito.');
        } catch (\Exception $e) {
            \Log::error('Error al aprobar médico: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al procesar la aprobación.');
        }
    }

    public function update(Request $request, $id)
    {
        $doctor = DoctorProfile::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'speciality_id' => 'required|exists:specialities,id',
            'clinic_address' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $doctor->user->update(['name' => $validated['name']]);
        $lat = isset($validated['latitude']) && $validated['latitude'] !== '' ? $validated['latitude'] : null;
        $lng = isset($validated['longitude']) && $validated['longitude'] !== '' ? $validated['longitude'] : null;
        $doctor->update([
            'speciality_id' => $validated['speciality_id'],
            'clinic_address' => $validated['clinic_address'],
            'phone_number' => $validated['phone_number'],
            'is_active' => $validated['is_active'],
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return redirect()->back()->with('message', 'Médico actualizado con éxito.');
    }

    public function destroy($id)
    {
        $doctor = DoctorProfile::findOrFail($id);
        $user = $doctor->user;

        $doctor->delete();
        $user->delete();

        return redirect()->back()->with('message', 'Médico y cuenta de usuario eliminados con éxito.');
    }
}
