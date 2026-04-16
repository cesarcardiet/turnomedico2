<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctorUser = User::role('doctor')->first();
        if (!$doctorUser)
            return;

        $doctorProfile = $doctorUser->doctorProfile;
        $patientUser = User::role('patient')->first();
        if (!$patientUser) {
            $patientUser = User::factory()->create();
            $patientUser->assignRole('patient');
        }

        // Create some time slots
        $dates = [now()->toDateString(), now()->addDay()->toDateString(), now()->addDays(2)->toDateString()];

        foreach ($dates as $date) {
            $slot = TimeSlot::create([
                'doctor_profile_id' => $doctorProfile->id,
                'date' => $date,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_booked' => true
            ]);

            Appointment::create([
                'user_id' => $patientUser->id,
                'doctor_profile_id' => $doctorProfile->id,
                'time_slot_id' => $slot->id,
                'problem_description' => 'Consulta general de prueba.',
                'status' => 'pending',
                'payment_status' => 'paid',
                'payment_method' => 'card'
            ]);
        }
    }
}
