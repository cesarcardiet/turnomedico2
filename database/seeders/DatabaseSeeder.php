<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 1. Seed Specialities
        $specialities = [
            ['name' => 'Cardiología', 'slug' => 'cardiologia', 'icon' => 'heart'],
            ['name' => 'Dermatología', 'slug' => 'dermatologia', 'icon' => 'user'],
            ['name' => 'Pediatría', 'slug' => 'pediatria', 'icon' => 'child'],
            ['name' => 'Neurología', 'slug' => 'neurologia', 'icon' => 'brain'],
            ['name' => 'Medicina General', 'slug' => 'medicina-general', 'icon' => 'stethoscope'],
        ];

        foreach ($specialities as $spec) {
            \App\Models\Speciality::updateOrCreate(['slug' => $spec['slug']], $spec);
        }

        // 2. Seed Membership Plans
        \App\Models\MembershipPlan::updateOrCreate(
            ['name' => 'Plan Premium'],
            [
                'description' => 'Acceso total para doctores.',
                'price' => 59.99,
                'duration_days' => 365,
                'is_active' => true
            ]
        );

        // 3. Seed Doctors (using the specialized seeder)
        $this->call([
            DoctorDataSeeder::class,
            TestCredentialsSeeder::class,
        ]);
    }
}
