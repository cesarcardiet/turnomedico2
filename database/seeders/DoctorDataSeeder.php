<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorDataSeeder extends Seeder
{
    public function run(): void
    {
        $specialities = Speciality::all();

        if ($specialities->isEmpty()) {
            return;
        }

        $cities = ['Santo Domingo', 'Santiago', 'La Romana', 'Punta Cana'];
        $surnames = ['Perez', 'García', 'Rodríguez', 'Martínez', 'López', 'González', 'Hernández'];

        foreach ($specialities as $spec) {
            // Create at least 2 doctors per speciality
            for ($i = 1; $i <= 2; $i++) {
                $name = "Dr. " . $surnames[array_rand($surnames)] . " (" . $spec->name . ")";
                // Clean name for email: remove accents, parents, dots, and spaces
                $cleanName = str_replace(['(', ')', '.', ' '], ['', '', '', '.'], $name);
                $cleanName = str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], $cleanName);
                $email = strtolower($cleanName) . "." . uniqid() . "@example.com";

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                ]);

                $user->assignRole('doctor');

                DoctorProfile::create([
                    'user_id' => $user->id,
                    'speciality_id' => $spec->id,
                    'about' => "Especialista con más de 10 años de experiencia en " . $spec->name . ". Dedicado a ofrecer una atención humana y profesional.",
                    'clinic_address' => $cities[array_rand($cities)] . ", Clínica " . $spec->name . " Central",
                    'phone_number' => '809-555-' . rand(1000, 9999),
                    'working_hours' => "Lun-Vie: 8:00 AM - 5:00 PM",
                    'is_approved' => true,
                    'is_active' => true,
                    'rating' => rand(4, 5),
                    'services' => ['Consulta General', 'Seguimiento', 'Procedimientos']
                ]);
            }
        }
    }
}
