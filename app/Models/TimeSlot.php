<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_profile_id', 'date', 'start_time', 'end_time', 'is_booked', 'slot_type'];

    public const SLOT_MORNING = 'morning';
    public const SLOT_AFTERNOON = 'afternoon';
    public const SLOT_NIGHT = 'night';

    public static function slotTypeLabel(string $type): string
    {
        return match ($type) {
            self::SLOT_MORNING => 'Mañana',
            self::SLOT_AFTERNOON => 'Tarde',
            self::SLOT_NIGHT => 'Noche',
            default => $type,
        };
    }

    protected $casts = [
        'date' => 'date',
        'is_booked' => 'boolean',
    ];

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
