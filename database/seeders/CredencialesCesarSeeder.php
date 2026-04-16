<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\MembershipPlan;
use App\Models\Speciality;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CredencialesCesarSeeder extends Seeder
{
    /**
     * Crea usuario administrador, doctor (cesar1) y paciente (cesar2) para pruebas.
     * Ejecutar: php artisan db:seed --class=CredencialesCesarSeeder
     */
    public function run(): void
    {
        // Asegurar que existan los roles (ejecutar antes RolesAndPermissionsSeeder si es necesario)
        if (! Role::where('name', 'admin')->exists()) {
            $this->call(RolesAndPermissionsSeeder::class);
        }

        // 1. Administrador
        $admin = User::updateOrCreate(
            ['email' => 'admin@turnomedico.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. Doctor: cesar1@gmail.com
        $speciality = Speciality::first() ?? Speciality::create(['name' => 'Medicina General', 'slug' => 'medicina-general', 'icon' => 'stethoscope']);

        $doctor = User::updateOrCreate(
            ['email' => 'cesar1@gmail.com'],
            [
                'name' => 'Dr. Cesar',
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
            ]
        );
        $doctor->syncRoles(['doctor']);

        $profile = DoctorProfile::updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'speciality_id' => $speciality->id,
                'about' => 'Médico con experiencia.',
                'clinic_address' => 'Clínica Ejemplo, Santo Domingo',
                'phone_number' => '809-555-0100',
                'consultation_fee' => 1500.00,
                'working_hours' => 'Lunes a Viernes 8:00 - 17:00',
                'services_description' => 'Consulta general.',
                'city' => 'Santo Domingo',
                'is_approved' => true,
                'is_active' => true,
            ]
        );

        $plan = MembershipPlan::first();
        if ($plan) {
            Subscription::updateOrCreate(
                ['user_id' => $doctor->id],
                [
                    'membership_plan_id' => $plan->id,
                    'payment_status' => 'approved',
                    'payment_method' => 'manual',
                    'reference_number' => 'REF-CESAR1',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays((int) $plan->duration_days),
                ]
            );
        }

        // 3. Paciente: cesar2@gmail.com
        $patient = User::updateOrCreate(
            ['email' => 'cesar2@gmail.com'],
            [
                'name' => 'Cesar Paciente',
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
            ]
        );
        $patient->syncRoles(['patient']);

        $this->command->info('Credenciales creadas/actualizadas:');
        $this->command->info('  Admin:    admin@turnomedico.com / Admin123!');
        $this->command->info('  Doctor:   cesar1@gmail.com / 123123123');
        $this->command->info('  Paciente: cesar2@gmail.com / 123123123');
    }
}
