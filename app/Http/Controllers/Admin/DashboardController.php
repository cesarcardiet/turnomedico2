<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Models\Review;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_appointments' => Appointment::count(),
                'total_doctors' => DoctorProfile::count(),
                'total_patients' => User::role('patient')->count(),
                'total_reviews' => Review::count(),
                'pending_doctors' => DoctorProfile::where('is_approved', false)->count(),
            ],
            'latest_appointments' => Appointment::with(['user', 'doctorProfile.user'])->latest()->limit(5)->get(),
        ]);
    }
}
