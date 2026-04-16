<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $appends = ['turn_number', 'patient_name', 'time'];

    protected $fillable = [
        'user_id',
        'doctor_profile_id',
        'time_slot_id',
        'problem_description',
        'status',
        'payment_status',
        'payment_method',
        'payment_proof'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function getTurnNumberAttribute()
    {
        if (!$this->relationLoaded('timeSlot')) {
            return null;
        }
        return self::where('doctor_profile_id', $this->doctor_profile_id)
            ->whereIn('status', ['pending', 'accepted', 'completed', 'in_consultation'])
            ->whereHas('timeSlot', function ($q) {
                $q->where('date', $this->timeSlot->date);
            })
            ->whereHas('timeSlot', function ($q) {
                $q->where('start_time', '<=', $this->timeSlot->start_time);
            })
            ->count();
    }

    public function getPatientNameAttribute()
    {
        return $this->user ? $this->user->name : 'N/A';
    }

    public function getTimeAttribute()
    {
        if (!$this->timeSlot) {
            return '--:--';
        }
        if ($this->timeSlot->slot_type) {
            return \App\Models\TimeSlot::slotTypeLabel($this->timeSlot->slot_type);
        }
        return $this->timeSlot->start_time ? substr($this->timeSlot->start_time, 0, 5) : '--:--';
    }
}
