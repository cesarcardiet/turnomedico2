<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $appointments = Appointment::with(['doctorProfile.user', 'doctorProfile.speciality', 'timeSlot'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->filter(function ($apt) {
                return $apt->timeSlot !== null;
            });

        $stats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'pending' => $appointments->whereIn('status', ['pending', 'accepted'])->count(),
        ];

        $today = now()->toDateString();
        $nextAppointment = $appointments
            ->filter(fn($a) => $a->timeSlot && $a->timeSlot->date >= $today && $a->status !== 'rejected')
            ->sortBy(fn($a) => ($a->timeSlot->date ?? '') . ' ' . ($a->timeSlot->start_time ?? ''))
            ->first();

        return Inertia::render('Patient/Dashboard', [
            'stats' => $stats,
            'appointments_by_date' => [
                'today' => $appointments->filter(fn($a) => $a->timeSlot && $a->timeSlot->date === $today)->values(),
                'upcoming' => $appointments->filter(fn($a) => $a->timeSlot && $a->timeSlot->date > $today)->values(),
                'past' => $appointments->filter(fn($a) => $a->timeSlot && $a->timeSlot->date < $today)->values(),
            ],
            'next_appointment' => $nextAppointment,
            'all_appointments' => $appointments->values(),
        ]);
    }
}
