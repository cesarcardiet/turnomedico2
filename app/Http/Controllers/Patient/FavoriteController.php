<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favorites()
            ->with(['doctorProfile.user', 'doctorProfile.speciality'])
            ->latest()
            ->get()
            ->map(function ($favorite) {
                return $favorite->doctorProfile;
            });

        return Inertia::render('Patient/Favorites', [
            'favorites' => $favorites
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_profile_id' => 'required|exists:doctor_profiles,id'
        ]);

        $exists = auth()->user()->favorites()->where('doctor_profile_id', $request->doctor_profile_id)->first();

        if ($exists) {
            $exists->delete();
            return back()->with('success', 'Médico eliminado de favoritos');
        }

        auth()->user()->favorites()->create([
            'doctor_profile_id' => $request->doctor_profile_id
        ]);

        return back()->with('success', 'Médico añadido a favoritos');
    }
}
