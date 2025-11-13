<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInspection extends Model
{
    protected $fillable = [
        'vehicle_id',
        'inspection_type',
        'inspection_station',
        'inspector_name',
        'document_url',
        'effective_date',
        'expiration_date',
        'status_id',
        'status_note',
        'is_passed',
        'failed_items',
        'inspector_notes',
        'is_verified',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_passed' => 'boolean',
        'failed_items' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
