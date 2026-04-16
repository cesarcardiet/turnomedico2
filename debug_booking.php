<?php
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\TimeSlot;
use App\Models\Appointment;
use App\Notifications\AppointmentBooked;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::role('patient')->first();
$doctor = DoctorProfile::with('user')->first();

if (!$user) {
    echo "No hay pacientes registrados.\n";
    exit;
}
if (!$doctor) {
    echo "No hay doctores registrados.\n";
    exit;
}

$slot = TimeSlot::where('doctor_profile_id', $doctor->id)->where('is_booked', false)->first();

if (!$slot) {
    echo "No hay horarios disponibles para el Dr. " . $doctor->user->name . "\n";
    exit;
}

echo "Simulando reserva para: " . $user->name . " con Dr. " . $doctor->user->name . "\n";
echo "Horario: " . $slot->date . " a las " . $slot->start_time . "\n";

try {
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'doctor_profile_id' => $doctor->id,
        'time_slot_id' => $slot->id,
        'problem_description' => 'Test de depuración',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'manual',
        'payment_proof' => 'test_proof.jpg'
    ]);

    $slot->update(['is_booked' => true]);
    echo "Cita creada ID: " . $appointment->id . "\n";

    echo "Enviando notificación...\n";
    $doctor->user->notify(new AppointmentBooked($appointment));
    echo "Notificación enviada con éxito.\n";

} catch (\Exception $e) {
    echo "ERROR DETECTADO: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . "\n";
    echo "LINE: " . $e->getLine() . "\n";
}
