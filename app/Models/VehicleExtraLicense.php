<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleExtraLicense extends Model
{
    protected $fillable = [
        'vehicle_id',
        'license_number',
        'type_id',
        'type_name',
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
}
