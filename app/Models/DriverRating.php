<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverRating extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'trip_id',
        'rating',
        'comment',
        'rated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
