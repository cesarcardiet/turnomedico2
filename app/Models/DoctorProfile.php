<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'speciality_id',
        'about',
        'clinic_address',
        'phone_number',
        'services',
        'working_hours',
        'consultation_fee',
        'services_description',
        'health_care_info',
        'city',
        'bank_name',
        'account_number',
        'account_holder',
        'bank_swift_ifsc',
        'rating',
        'is_approved',
        'is_active',
        'latitude',
        'longitude',
        'max_turns_per_day'
    ];

    public function salesStoppedDates()
    {
        return $this->hasMany(DoctorSalesStopped::class);
    }

    protected $appends = [
        'profile_photo_url',
    ];

    protected $casts = [
        'services' => 'array',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function weeklySchedules()
    {
        return $this->hasMany(DoctorWeeklySchedule::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function isComplete(): bool
    {
        return trim((string) ($this->about ?? '')) !== ''
            && trim((string) ($this->clinic_address ?? '')) !== ''
            && trim((string) ($this->phone_number ?? '')) !== ''
            && $this->speciality_id !== null
            && (string) $this->speciality_id !== ''
            && (int) $this->speciality_id > 0;
    }

    /**
     * Campos obligatorios para perfil completo (claves para mensajes).
     * @return array<string>
     */
    public function getMissingRequiredFields(): array
    {
        $missing = [];
        if (trim((string) ($this->about ?? '')) === '') {
            $missing[] = 'about';
        }
        if (trim((string) ($this->clinic_address ?? '')) === '') {
            $missing[] = 'clinic_address';
        }
        if (trim((string) ($this->phone_number ?? '')) === '') {
            $missing[] = 'phone_number';
        }
        if ($this->speciality_id === null || (string) $this->speciality_id === '' || (int) $this->speciality_id <= 0) {
            $missing[] = 'speciality';
        }
        return $missing;
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->user ? $this->user->profile_photo_url : null;
    }
}
