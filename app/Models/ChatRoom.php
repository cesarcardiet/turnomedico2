<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_profile_id',
        'last_message',
        'last_message_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
