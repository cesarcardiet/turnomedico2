<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'image_path'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image_path)
            return null;

        return filter_var($this->image_path, FILTER_VALIDATE_URL)
            ? $this->image_path
            : \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path);
    }

    public function doctorProfiles()
    {
        return $this->hasMany(DoctorProfile::class);
    }
}
