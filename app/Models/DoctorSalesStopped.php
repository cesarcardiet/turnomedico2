<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSalesStopped extends Model
{
    use HasFactory;

    protected $table = 'doctor_sales_stopped';

    protected $fillable = ['doctor_profile_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
