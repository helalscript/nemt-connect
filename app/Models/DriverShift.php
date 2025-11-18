<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverShift extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'shift_type_id',
        'shift_start_time',
        'shift_end_time',
        'shift_date',
        'shift_notes',
    ];
    protected $casts = [
        'shift_start_time' => 'time',
        'shift_end_time' => 'time',
        'shift_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function getShiftDurationAttribute()
    {
        return $this->shift_end_time->diffInHours($this->shift_start_time);
    }
}
