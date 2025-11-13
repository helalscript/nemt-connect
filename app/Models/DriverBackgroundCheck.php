<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverBackgroundCheck extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'document_url',
        'effective_date',
        'expiration_date',
        'status_id',
        'status_note',
        'check_provider',
        'confirmation_number',
        'is_cleared',
        'is_verified',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_cleared' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }
}
