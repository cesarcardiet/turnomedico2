<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar datos previos para evitar duplicados en pruebas
        User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->delete();

        $specialities = Speciality::all();
        if ($specialities->isEmpty()) {
            $specialities = collect([
                Speciality::create(['name' => 'Cardiología', 'slug' => 'cardiologia']),
                Speciality::create(['name' => 'Pediatría', 'slug' => 'pediatria']),
                Speciality::create(['name' => 'Dermatología', 'slug' => 'dermatologia']),
                Speciality::create(['name' => 'Ginecología', 'slug' => 'ginecologia']),
            ]);
        }

        $doctorsData = [
            ['name' => 'Dr. Pedro Cesar', 'spec' => 'Cardiología', 'city' => 'Santiago, RD', 'fee' => '2500'],
            ['name' => 'Dra. Elena Martínez', 'spec' => 'Pediatría', 'city' => 'Santo Domingo, RD', 'fee' => '1800'],
            ['name' => 'Dr. Manuel Rodríguez', 'spec' => 'Dermatología', 'city' => 'Santiago, RD', 'fee' => '2200'],
            ['name' => 'Dra. Laura González', 'spec' => 'Ginecología', 'city' => 'La Romana, RD', 'fee' => '3000'],
            ['name' => 'Dr. Ricardo Pérez', 'spec' => 'Cardiología', 'city' => 'Santo Domingo, RD', 'fee' => '2500'],
        ];

        foreach ($doctorsData as $data) {
            $spec = Speciality::where('name', $data['spec'])->first() ?? $specialities->first();

            $cleanName = str_replace(['Dr. ', 'Dra. ', ' '], ['', '', '.'], $data['name']);
            $email = strtolower($cleanName) . "." . uniqid() . "@turnomedico.com";

            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('doctor');

            $profile = DoctorProfile::create([
                'user_id' => $user->id,
                'speciality_id' => $spec->id,
                'about' => "Médico especialista en " . strtolower($spec->name) . " con más de 10 años de experiencia. Dedicación exclusiva a la salud de mis pacientes con tecnología de vanguardia.",
                'clinic_address' => $data['city'] . ", Centro Médico Profesional, Suite " . rand(100, 500),
                'phone_number' => '809-555-' . rand(1000, 9999),
                'working_hours' => "Lun-Vie: 8:00 AM - 6:00 PM",
                'is_approved' => true,
                'is_active' => true,
                'rating' => 5,
                'consultation_fee' => $data['fee'],
                'bank_name' => 'Banco Popular',
                'account_number' => '12' . rand(10000000, 99999999),
                'account_holder' => $data['name'],
                'services' => ['Consulta General', 'Seguimiento especializado', 'Revisión de estudios']
            ]);

            // Crear TimeSlots para las próximas 2 semanas
            for ($i = 0; $i < 14; $i++) {
                $date = Carbon::now()->addDays($i);
                if ($date->isWeekend())
                    continue;

                $hours = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
                foreach ($hours as $hour) {
                    TimeSlot::create([
                        'doctor_profile_id' => $profile->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $hour . ':00',
                        'end_time' => Carbon::parse($hour)->addHour()->format('H:i:s'),
                        'is_booked' => false
                    ]);
                }
            }
        }
    }
}
