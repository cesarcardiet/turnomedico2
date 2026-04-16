<?php
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\DoctorWeeklySchedule;
use App\Models\TimeSlot;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'doctor@turnomedico.com')->first();
if (!$user) {
    echo "Doctor not found\n";
    exit;
}

$p = $user->doctorProfile;

// 1. Create Weekly Schedules if they don't exist
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
foreach ($days as $day) {
    DoctorWeeklySchedule::updateOrCreate(
        ['doctor_profile_id' => $p->id, 'day_of_week' => $day],
        ['start_time' => '09:00:00', 'end_time' => '17:00:00', 'slot_duration' => 30]
    );
}

echo "Weekly schedules created for Lunes a Viernes.\n";

// 2. Clear existing slots to avoid mess
$p->timeSlots()->delete();

// 3. Generate slots for next 14 days
$startDate = now();
$endDate = now()->addDays(14);

$count = 0;
for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
    $dayName = strtolower($date->format('l'));
    $schedules = DoctorWeeklySchedule::where('doctor_profile_id', $p->id)
        ->where('day_of_week', $dayName)
        ->get();

    foreach ($schedules as $s) {
        $start = Carbon::parse($date->toDateString() . ' ' . $s->start_time);
        $end = Carbon::parse($date->toDateString() . ' ' . $s->end_time);
        $duration = (int) $s->slot_duration;

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            // Only create if it's in the future
            if ($start->isFuture()) {
                TimeSlot::create([
                    'doctor_profile_id' => $p->id,
                    'date' => $date->toDateString(),
                    'start_time' => $start->toTimeString(),
                    'end_time' => $start->copy()->addMinutes($duration)->toTimeString(),
                    'is_booked' => false
                ]);
                $count++;
            }
            $start->addMinutes($duration);
        }
    }
}

echo "Generated $count slots for Dr. Pedro Cesar.\n";
echo "Done! Refresh the page now.\n";
