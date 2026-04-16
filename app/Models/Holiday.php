<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'doctor_profile_id',
        'date',
        'reason',
        'is_active'
    ];

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
