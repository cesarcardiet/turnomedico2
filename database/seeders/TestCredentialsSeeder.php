<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Speciality;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Hash;
use Illuminate\Support\Str;

class TestCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@turnomedico.com'],
            [
                'name' => 'Administrador Turno Médico',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // 2. Doctor User
        $doctor = User::updateOrCreate(
            ['email' => 'doctor@turnomedico.com'],
            [
                'name' => 'Dr. Pedro Cesar',
                'password' => Hash::make('doctor123'),
                'email_verified_at' => now(),
            ]
        );
        $doctor->assignRole('doctor');

        // Ensure speciality exists with slug
        $speciality = Speciality::updateOrCreate(
            ['name' => 'Cardiología'],
            ['slug' => 'cardiologia']
        );

        // Create/Update Doctor Profile
        $profile = DoctorProfile::updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'speciality_id' => $speciality->id,
                'about' => 'Médico especialista en cardiología con más de 10 años de experiencia.',
                'clinic_address' => 'Calle 123, Centro Médico Central',
                'phone_number' => '809-555-5555',
                'consultation_fee' => 2500.00,
                'working_hours' => 'Lunes a Viernes: 08:00 AM - 05:00 PM',
                'services_description' => 'Evaluación cardiovascular, ECG, Holter, Ecocardiografía.',
                'city' => 'Santo Domingo',
                'bank_name' => 'Banco Popular',
                'account_number' => '1234567890',
                'account_holder' => 'Pedro Cesar',
                'bank_swift_ifsc' => 'BPOPDO',
                'is_approved' => true,
                'is_active' => true,
            ]
        );

        // Ensure Membership Plan exists with duration_days
        $plan = MembershipPlan::updateOrCreate(
            ['name' => 'Plan Premium'],
            [
                'description' => 'Acceso total a todas las herramientas del panel.',
                'price' => 2000,
                'duration_days' => 365,
                'is_active' => true
            ]
        );

        // Active Subscription for Doctor
        Subscription::updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'membership_plan_id' => $plan->id,
                'payment_status' => 'approved',
                'payment_method' => 'manual',
                'reference_number' => 'REF-SEED-123',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
            ]
        );
    }
}
