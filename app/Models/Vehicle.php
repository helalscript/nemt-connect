<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes,HasFactory;

    protected $fillable = [
        'sentry_vehicle_id',
        'vin',
        'fleet_number',
        'production_year',
        'type_id',
        'type_name',
        'make',
        'model',
        'color',
        'seat_number',
        'status_id',
        'owner_driver_profile_id',
        'ownership_type',
        'is_wheelchair_accessible',
        'is_stretcher_accessible',
        'has_wheelchair_lift',
        'has_wheelchair_ramp',
        'wheelchair_capacity',
        'has_oxygen',
        'has_air_conditioning',
        'has_gps',
        'is_available',
        'is_in_use',
        'current_driver_id',
        'current_trip_id',
        'current_lat',
        'current_lng',
        'location_updated_at',
        'credentials_status_id',
        'credentials_status_note',
        'odometer_reading',
        'last_maintenance_date',
        'next_maintenance_due',
        'maintenance_interval_miles',
        'photo_front',
        'photo_back',
        'photo_side',
        'photo_interior',
        'total_trips_completed',
        'total_miles_driven',
        'average_rating',
        'breakdown_count',
    ];

    protected $casts = [
        'production_year' => 'integer',
        'seat_number' => 'integer',
        'is_wheelchair_accessible' => 'boolean',
        'is_stretcher_accessible' => 'boolean',
        'has_wheelchair_lift' => 'boolean',
        'has_wheelchair_ramp' => 'boolean',
        'wheelchair_capacity' => 'integer',
        'has_oxygen' => 'boolean',
        'has_air_conditioning' => 'boolean',
        'has_gps' => 'boolean',
        'is_available' => 'boolean',
        'is_in_use' => 'boolean',
        'location_updated_at' => 'datetime',
        'last_maintenance_date' => 'date',
        'next_maintenance_due' => 'date',
        'total_miles_driven' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    // ============ Relationships ============

    public function ownerDriver()
    {
        return $this->belongsTo(DriverProfile::class, 'owner_driver_id');
    }

    public function currentDriver()
    {
        return $this->belongsTo(DriverProfile::class, 'current_driver_id');
    }

    public function currentTrip()
    {
        return $this->belongsTo(Trip::class, 'current_trip_id');
    }

    public function dmvRegistration()
    {
        return $this->hasOne(VehicleDmvRegistration::class);
    }

    public function extraLicenses()
    {
        return $this->hasMany(VehicleExtraLicense::class);
    }

    public function insurance()
    {
        return $this->hasOne(VehicleInsurance::class)->latest();
    }

    public function insurances()
    {
        return $this->hasMany(VehicleInsurance::class);
    }

    public function inspection()
    {
        return $this->hasOne(VehicleInspection::class)->latest();
    }

    public function inspections()
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenanceRecord::class);
    }

    public function defects()
    {
        return $this->hasMany(VehicleDefect::class);
    }

    public function openDefects()
    {
        return $this->hasMany(VehicleDefect::class)->where('status', 'open');
    }

    public function driverAssignments()
    {
        return $this->hasMany(DriverVehicleAssignment::class);
    }

    public function activeDriverAssignment()
    {
        return $this->hasOne(DriverVehicleAssignment::class)->where('is_active', true);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function completedTrips()
    {
        return $this->hasMany(Trip::class)->where('status', 'completed');
    }

    // ============ Accessors ============

    public function getFullNameAttribute()
    {
        return "{$this->production_year} {$this->make} {$this->model}";
    }

    public function getStatusNameAttribute()
    {
        return match ($this->status_id) {
            1 => 'Active',
            2 => 'Inactive',
            3 => 'Under Maintenance',
            4 => 'Out of Service',
            5 => 'Retired',
            default => 'Unknown'
        };
    }

    public function getTypeNameAttribute()
    {
        return match ($this->type_id) {
            1 => 'Ambulatory',
            2 => 'Wheelchair Accessible',
            3 => 'Stretcher',
            4 => 'Ambulance',
            5 => 'Van',
            default => 'Unknown'
        };
    }

    public function getIsCredentialsCompleteAttribute()
    {
        return $this->dmvRegistration()->exists()
            && $this->insurance()->exists()
            && $this->inspection()->exists()
            && $this->dmvRegistration->status_id == 1
            && $this->insurance->status_id == 1
            && $this->inspection->status_id == 1;
    }

    public function getHasActiveDefectsAttribute()
    {
        return $this->openDefects()->exists();
    }

    public function getNeedsMaintenanceAttribute()
    {
        if (!$this->next_maintenance_due) {
            return false;
        }
        return $this->next_maintenance_due <= today()->addDays(7);
    }

    // ============ Scopes ============

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('status_id', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status_id', 1);
    }

    public function scopeWheelchairAccessible($query)
    {
        return $query->where('is_wheelchair_accessible', true);
    }

    public function scopeStretcherAccessible($query)
    {
        return $query->where('is_stretcher_accessible', true);
    }

    public function scopeInUse($query)
    {
        return $query->where('is_in_use', true);
    }

    public function scopeOwnedBy($query, $driverId)
    {
        return $query->where('owner_driver_id', $driverId);
    }

    // ============ Methods ============

    public function updateLocation($lat, $lng)
    {
        $this->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'location_updated_at' => now()
        ]);
    }

    public function markInUse($tripId = null, $driverId = null)
    {
        $this->update([
            'is_in_use' => true,
            'is_available' => false,
            'current_trip_id' => $tripId,
            'current_driver_id' => $driverId
        ]);
    }

    public function markAvailable()
    {
        $this->update([
            'is_in_use' => false,
            'is_available' => true,
            'current_trip_id' => null
        ]);
    }

    public function recordMileage($miles)
    {
        $this->increment('total_miles_driven', $miles);
        $this->update([
            'odometer_reading' => ($this->odometer_reading ?? 0) + $miles
        ]);
    }
}
