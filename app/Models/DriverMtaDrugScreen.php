<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverMtaDrugScreen extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'document_url',
        'result_verification_date',
        'expiration_date',
        'status_id',
        'status_note',
        'test_facility',
        'confirmation_number',
        'is_passed',
        'is_verified',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'result_verification_date' => 'date',
        'expiration_date' => 'date',
        'is_passed' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }
}
