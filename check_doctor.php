<?php
use App\Models\User;
use App\Models\TimeSlot;
use App\Models\DoctorWeeklySchedule;

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
echo "Profile ID: " . $p->id . "\n";
echo "Doctor Name: " . $user->name . "\n";
$weekly = DoctorWeeklySchedule::where('doctor_profile_id', $p->id)->get();
echo "Weekly Schedules: " . $weekly->count() . "\n";
foreach ($weekly as $s) {
    echo " - " . $s->day_of_week . ": " . $s->start_time . " to " . $s->end_time . "\n";
}

$slots = $p->timeSlots()->where('date', '>=', now()->toDateString())->count();
echo "Actual Time Slots (future): " . $slots . "\n";
if ($slots > 0) {
    $first = $p->timeSlots()->where('date', '>=', now()->toDateString())->orderBy('date')->first();
    echo " - First future slot: " . $first->date . " at " . $first->start_time . "\n";
}

$slotsToday = $p->timeSlots()->where('date', now()->toDateString())->count();
echo "Actual Time Slots (today): " . $slotsToday . "\n";
