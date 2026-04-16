<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorProfile::with(['user', 'speciality'])
            ->where('is_approved', true)
            ->where('is_active', true)
            ->whereNotNull('about')
            ->whereNotNull('clinic_address')
            ->whereNotNull('phone_number');

        // Search by name
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by speciality
        if ($request->filled('speciality')) {
            $query->where('speciality_id', $request->speciality);
        }

        // Filter by city (doctor_profiles.city)
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $doctors = $query->latest()->get()->filter(fn($d) => $d->isComplete())->values();

        // Filter by location (lat, lng, radius_km) - solo doctores con coordenadas, filtro en PHP para compatibilidad
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $radiusKm = (float) ($request->input('radius_km', 25));
        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng) && $radiusKm > 0) {
            $lat = (float) $lat;
            $lng = (float) $lng;
            $doctors = $doctors->filter(function ($doctor) use ($lat, $lng, $radiusKm) {
                $dlat = $doctor->latitude ? (float) $doctor->latitude : null;
                $dlng = $doctor->longitude ? (float) $doctor->longitude : null;
                if ($dlat === null || $dlng === null) {
                    return false;
                }
                return $this->haversineDistanceKm($lat, $lng, $dlat, $dlng) <= $radiusKm;
            })->values();
        }

        return Inertia::render('Patient/Search', [
            'doctors' => $doctors,
            'specialities' => Speciality::all(),
            'cities' => \App\Models\City::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'speciality', 'city', 'lat', 'lng', 'radius_km']),
            'google_maps_api_key' => \App\Models\Setting::get('google_maps_api_key'),
        ]);
    }

    public function show($id)
    {
        $doctor = DoctorProfile::with([
            'user',
            'speciality',
            'reviews.user',
            'timeSlots' => function ($query) use ($id) {
                $query->where('is_booked', false)
                    ->where('date', '>=', now()->toDateString())
                    ->whereNotIn('date', function ($q) use ($id) {
                        $q->select('date')->from('doctor_sales_stopped')->where('doctor_profile_id', $id);
                    });
            }
        ])->findOrFail($id);

        return Inertia::render('Patient/DoctorProfile', [
            'doctor' => $doctor,
        ]);
    }

    public function autocomplete(Request $request)
    {
        $search = $request->query('q');

        if (empty($search)) {
            return response()->json([]);
        }

        $doctors = DoctorProfile::with('user', 'speciality')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'speciality' => $doctor->speciality->name ?? '',
                ];
            });

        return response()->json($doctors);
    }

    /**
     * Distance in km between two points (Haversine formula).
     */
    private function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
