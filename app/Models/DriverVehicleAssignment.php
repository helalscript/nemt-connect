<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverVehicleAssignment extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'vehicle_id',
        'is_primary',
        'is_active',
        'assigned_at',
        'unassigned_at',
        'assigned_by',
        'shift_start',
        'shift_end',
        'assignment_notes',
    ];
    
    protected $casts = [
        'is_primary'    => 'boolean',
        'is_active'     => 'boolean',
        'assigned_at'   => 'datetime',
        'unassigned_at' => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
