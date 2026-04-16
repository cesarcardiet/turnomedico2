<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\DoctorWeeklySchedule;
use App\Models\DoctorSalesStopped;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = $user->doctorProfile;

        $timeSlots = TimeSlot::where('doctor_profile_id', $profile->id)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $weeklySchedules = DoctorWeeklySchedule::where('doctor_profile_id', $profile->id)->get();

        $salesStoppedDates = DoctorSalesStopped::where('doctor_profile_id', $profile->id)
            ->where('date', '>=', now()->toDateString())
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();

        return Inertia::render('Doctor/Schedule', [
            'timeSlots' => $timeSlots,
            'weeklySchedules' => $weeklySchedules,
            'maxTurnsPerDay' => $profile->max_turns_per_day,
            'salesStoppedDates' => $salesStoppedDates,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $doctorProfileId = auth()->user()->doctorProfile->id;

        $overlap = TimeSlot::where('doctor_profile_id', $doctorProfileId)
            ->where('date', $request->date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()->withErrors(['start_time' => 'Ya existe un turno en este horario.']);
        }

        TimeSlot::create([
            'doctor_profile_id' => $doctorProfileId,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_booked' => false
        ]);

        return redirect()->back()->with('message', 'Horario agregado correctamente.');
    }

    public function storeWeekly(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
        ]);

        $doctorProfileId = auth()->user()->doctorProfile->id;

        DoctorWeeklySchedule::create([
            'doctor_profile_id' => $doctorProfileId,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_duration' => $request->slot_duration,
        ]);

        return redirect()->back()->with('message', 'Horario semanal configurado.');
    }

    public function destroyWeekly($id)
    {
        $schedule = DoctorWeeklySchedule::findOrFail($id);
        $schedule->delete();
        return redirect()->back()->with('message', 'Horario semanal eliminado.');
    }

    public function generateTimeSlots(Request $request)
    {
        $user = auth()->user();
        $doctorProfileId = $user->doctorProfile->id;
        $weeklySchedules = DoctorWeeklySchedule::where('doctor_profile_id', $doctorProfileId)->get();

        if ($weeklySchedules->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'Configura primero tu horario semanal.']);
        }

        $startDate = now();
        $endDate = now()->addDays(14);

        $count = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->format('l'));
            $daySchedules = $weeklySchedules->where('day_of_week', $dayName);

            foreach ($daySchedules as $schedule) {
                $start = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
                $end = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);
                $duration = (int) $schedule->slot_duration;

                while ($start->copy()->addMinutes($duration)->lte($end)) {
                    $exists = TimeSlot::where('doctor_profile_id', $doctorProfileId)
                        ->where('date', $date->toDateString())
                        ->where('start_time', $start->toTimeString())
                        ->exists();

                    if (!$exists) {
                        TimeSlot::create([
                            'doctor_profile_id' => $doctorProfileId,
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

        return redirect()->back()->with('message', "Se han generado $count nuevos turnos para los próximos 14 días.");
    }

    public function destroy($id)
    {
        $slot = TimeSlot::findOrFail($id);

        $authUser = auth()->user();
        $authProfile = $authUser?->doctorProfile;

        \Log::info('Doctor intenta eliminar turno', [
            'slot_id' => $slot->id,
            'slot_doctor_profile_id' => $slot->doctor_profile_id,
            'auth_user_id' => $authUser?->id,
            'auth_doctor_profile_id' => $authProfile->id ?? null,
        ]);

        if (!$authProfile || (int) $slot->doctor_profile_id !== (int) $authProfile->id) {
            \Log::warning('Intento de eliminar turno que no pertenece al doctor autenticado', [
                'slot_id' => $slot->id,
                'slot_doctor_profile_id' => $slot->doctor_profile_id,
                'auth_user_id' => $authUser?->id,
                'auth_doctor_profile_id' => $authProfile->id ?? null,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'No puedes eliminar un turno que no pertenece a tu agenda.',
            ]);
        }
        if ($slot->is_booked) {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminar un turno que ya ha sido reservado.']);
        }

        $slot->delete();

        \Log::info('Turno eliminado correctamente', [
            'slot_id' => $slot->id,
        ]);

        return redirect()->back()->with('message', 'Horario eliminado.');
    }

    /**
     * Agregar turnos por rango de días (fecha desde → hasta) y bloques Mañana/Tarde/Noche.
     */
    public function storeByDayRange(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after_or_equal:date_from',
            'blocks' => 'required|array',
            'blocks.*' => 'in:morning,afternoon,night',
            'turns_per_block' => 'nullable|integer|min:1|max:50',
        ]);

        $profileId = auth()->user()->doctorProfile->id;
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        if ($dateTo->diffInDays($dateFrom) > 90) {
            return redirect()->back()->withErrors(['error' => 'El rango no puede ser mayor a 90 días.']);
        }

        $blocks = array_unique($request->blocks);
        $turnsPerBlock = (int) ($request->turns_per_block ?? 5);
        if ($turnsPerBlock < 1) {
            $turnsPerBlock = 5;
        }

        $baseTimes = [
            'morning' => ['08:00', '12:00'],
            'afternoon' => ['12:00', '18:00'],
            'night' => ['18:00', '22:00'],
        ];

        $totalCount = 0;
        $duration = 30;

        for ($date = $dateFrom->copy(); $date->lte($dateTo); $date->addDay()) {
            $dateStr = $date->toDateString();

            foreach ($blocks as $slotType) {
                $start = Carbon::parse($dateStr . ' ' . $baseTimes[$slotType][0]);
                $end = Carbon::parse($dateStr . ' ' . $baseTimes[$slotType][1]);
                $created = 0;

                while ($created < $turnsPerBlock && $start->copy()->addMinutes($duration)->lte($end)) {
                    $exists = TimeSlot::where('doctor_profile_id', $profileId)
                        ->where('date', $dateStr)
                        ->where('slot_type', $slotType)
                        ->where('start_time', $start->toTimeString())
                        ->exists();

                    if (!$exists) {
                        TimeSlot::create([
                            'doctor_profile_id' => $profileId,
                            'date' => $dateStr,
                            'start_time' => $start->toTimeString(),
                            'end_time' => $start->copy()->addMinutes($duration)->toTimeString(),
                            'is_booked' => false,
                            'slot_type' => $slotType,
                        ]);
                        $totalCount++;
                        $created++;
                    }
                    $start->addMinutes($duration);
                }
            }
        }

        $labels = array_map(fn ($b) => TimeSlot::slotTypeLabel($b), $blocks);
        return redirect()->back()->with('message', "Turnos agregados para el rango de fechas: " . implode(', ', $labels) . " ($totalCount turnos en total).");
    }

    /**
     * Agregar turnos por un solo día y bloque (Mañana, Tarde, Noche).
     */
    public function storeByDay(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'blocks' => 'required|array',
            'blocks.*' => 'in:morning,afternoon,night',
            'turns_per_block' => 'nullable|integer|min:1|max:50',
        ]);

        $profileId = auth()->user()->doctorProfile->id;
        $date = $request->date;
        $blocks = array_unique($request->blocks);
        $turnsPerBlock = (int) ($request->turns_per_block ?? 5);
        if ($turnsPerBlock < 1) {
            $turnsPerBlock = 5;
        }

        $baseTimes = [
            'morning' => ['08:00', '12:00'],
            'afternoon' => ['12:00', '18:00'],
            'night' => ['18:00', '22:00'],
        ];

        $count = 0;
        $duration = 30;

        foreach ($blocks as $slotType) {
            $start = Carbon::parse($date . ' ' . $baseTimes[$slotType][0]);
            $end = Carbon::parse($date . ' ' . $baseTimes[$slotType][1]);
            $created = 0;

            while ($created < $turnsPerBlock && $start->copy()->addMinutes($duration)->lte($end)) {
                $exists = TimeSlot::where('doctor_profile_id', $profileId)
                    ->where('date', $date)
                    ->where('slot_type', $slotType)
                    ->where('start_time', $start->toTimeString())
                    ->exists();

                if (!$exists) {
                    TimeSlot::create([
                        'doctor_profile_id' => $profileId,
                        'date' => $date,
                        'start_time' => $start->toTimeString(),
                        'end_time' => $start->copy()->addMinutes($duration)->toTimeString(),
                        'is_booked' => false,
                        'slot_type' => $slotType,
                    ]);
                    $count++;
                    $created++;
                }
                $start->addMinutes($duration);
            }
        }

        $labels = array_map(fn ($b) => TimeSlot::slotTypeLabel($b), $blocks);
        return redirect()->back()->with('message', 'Turnos agregados: ' . implode(', ', $labels) . " ($count turnos).");
    }

    /**
     * Detener la venta de turnos para una fecha (el paciente ya no verá slots ese día).
     */
    public function stopSales(Request $request)
    {
        $request->validate(['date' => 'required|date|after_or_equal:today']);
        $profileId = auth()->user()->doctorProfile->id;

        DoctorSalesStopped::firstOrCreate(
            ['doctor_profile_id' => $profileId, 'date' => $request->date],
            ['doctor_profile_id' => $profileId, 'date' => $request->date]
        );

        return redirect()->back()->with('message', 'Venta de turnos detenida para esa fecha.');
    }

    /**
     * Reanudar la venta de turnos para una fecha.
     */
    public function resumeSales(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $profileId = auth()->user()->doctorProfile->id;

        DoctorSalesStopped::where('doctor_profile_id', $profileId)->where('date', $request->date)->delete();

        return redirect()->back()->with('message', 'Venta de turnos reanudada para esa fecha.');
    }
}
