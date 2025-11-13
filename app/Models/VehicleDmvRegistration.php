<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDmvRegistration extends Model
{
     protected $fillable = [
        'vehicle_id',
        'license_plate_number',
        'license_plate_category_id',
        'state_code',
        'document_url',
        'effective_date',
        'expiration_date',
        'status_id',
        'status_note',
        'is_verified',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    public function getIsExpiredAttribute()
    {
        return $this->expiration_date < today();
    }
    
    public function getDaysUntilExpiryAttribute()
    {
        return today()->diffInDays($this->expiration_date, false);
    }
}
