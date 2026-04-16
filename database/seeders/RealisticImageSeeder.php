<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use Illuminate\Database\Seeder;

class RealisticImageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Update Speciality Images (Verified Ultra-Stable URLs)
        $specImages = [
            'cardiologia' => 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?q=80&w=400&auto=format&fit=crop',
            'dermatologia' => 'https://images.unsplash.com/photo-1615811361523-6bd03d7748e7?q=80&w=400&auto=format&fit=crop',
            'pediatria' => 'https://images.unsplash.com/photo-1631217816690-d54dfb38f822?q=80&w=400&auto=format&fit=crop',
            'neurologia' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?q=80&w=400&auto=format&fit=crop',
            'medicina-general' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?q=80&w=400&auto=format&fit=crop',
        ];

        foreach ($specImages as $slug => $url) {
            Speciality::where('slug', $slug)->update(['image_path' => $url]);
        }

        // 2. Verified Doctor Images
        $doctorImages = [
            'https://images.unsplash.com/photo-1537368910025-700350fe46c7?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1559839734-2b71f1536780?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1594824476967-48c8b964273f?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1550831107-1553da8c8464?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1614608682850-e0ad6edb658e?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1625017017027-e109bd497c19?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1582750433449-648ed127bb54?q=80&w=400&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1584467735815-f778f274e296?q=80&w=400&auto=format&fit=crop',
        ];

        // 3. Update ALL doctors to ensure no one is missed
        $doctors = User::role('doctor')->get();
        foreach ($doctors as $index => $user) {
            $user->update([
                'profile_photo_path' => $doctorImages[$index % count($doctorImages)]
            ]);
        }

        // 4. Force specific ones mentioned by user just in case
        User::where('name', 'like', '%López%')->where('name', 'like', '%Cardiología%')->first()?->update([
            'profile_photo_path' => 'https://images.unsplash.com/photo-1559839734-2b71f1536780?q=80&w=400&auto=format&fit=crop'
        ]);

        User::where('name', 'like', '%Rodríguez%')->where('name', 'like', '%Medicina General%')->first()?->update([
            'profile_photo_path' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop'
        ]);

        User::where('name', 'like', '%Perez%')->where('name', 'like', '%Medicina General%')->first()?->update([
            'profile_photo_path' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?q=80&w=400&auto=format&fit=crop'
        ]);
    }
}
