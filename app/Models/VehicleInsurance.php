<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'policy_number',
        'insurer_name',
        'agent_name',
        'agent_phone',
        'document_url',
        'effective_date',
        'expiration_date',
        'liability_coverage',
        'collision_coverage',
        'comprehensive_coverage',
        'status_id',
        'status_note',
        'is_verified',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'liability_coverage' => 'decimal:2',
        'collision_coverage' => 'decimal:2',
        'comprehensive_coverage' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
