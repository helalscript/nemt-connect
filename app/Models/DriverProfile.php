<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'sentry_driver_id',
        'first_name',
        'last_name',
        'social_security_number',
        'gender',
        'birth_date',
        'phone',
        // 'email',
        // 'password',
        // 'profile_photo',
        // 'address',
        // 'city',
        // 'state_code',
        // 'zip_code',
        'working_area_name',
        'working_area_lat',
        'working_area_lng',
        'working_area_radius',
        'status_id',
        'is_online',
        'is_available',
        'last_active_at',
        'current_lat',
        'current_lng',
        'current_speed',
        'current_heading',
        'location_updated_at',
        'current_vehicle_id',
        'credentials_status_id',
        'credentials_status_note',
        'is_admin_verified',
        'admin_verified_at',
        'admin_verified_by',
        'admin_verified_note',
        'is_approved_for_mta',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'bank_account_holder_name',
        'bank_name',
        'bank_account_number',
        'bank_routing_number',
    ];

    protected $casts = [
        'is_admin_verified'              => 'boolean',
        'admin_verified_at'              => 'datetime',
        'is_approved_for_mta'            => 'boolean',
        'emergency_contact_phone'        => 'string',
        'emergency_contact_relationship' => 'string',
        'bank_account_number'            => 'string',
        'bank_routing_number'            => 'string',
        'birth_date'                     => 'date',
        'last_active_at'                 => 'datetime',
        'location_updated_at'            => 'datetime',
        'current_lat'                    => 'decimal:7',
        'current_lng'                    => 'decimal:7',
        'current_speed'                  => 'decimal:2',
        'current_heading'                => 'string',
        'current_vehicle_id'             => 'string',
        'credentials_status_id'          => 'integer',
        'credentials_status_note'        => 'string',
        'admin_verified_by'              => 'string',
        'admin_verified_note'            => 'string',
        'bank_account_holder_name'       => 'string',
        'bank_name'                      => 'string',
    ];

    // ============ Relationships ============

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dmvLicense()
    {
        return $this->hasOne(DriverDmvLicense::class);
    }

    public function extraLicenses()
    {
        return $this->hasMany(DriverExtraLicense::class);
    }

    public function backgroundCheck()
    {
        return $this->hasOne(DriverBackgroundCheck::class);
    }

    public function mtaDrugScreen()
    {
        return $this->hasOne(DriverMtaDrugScreen::class);
    }

    public function certifications()
    {
        return $this->hasMany(DriverCertification::class);
    }

    public function currentVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'current_vehicle_id');
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(DriverVehicleAssignment::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function activeTrips()
    {
        return $this->hasMany(Trip::class)->whereIn('status', [
            'assigned',
            'accepted',
            'en_route_to_pickup',
            'arrived_at_pickup',
            'patient_on_board',
            'en_route_to_dropoff',
            'arrived_at_dropoff',
        ]);
    }

    public function completedTrips()
    {
        return $this->hasMany(Trip::class)->where('status', 'completed');
    }

    public function shifts()
    {
        return $this->hasMany(DriverShift::class);
    }

    public function ratings()
    {
        return $this->hasMany(DriverRating::class);
    }

    // ============ Accessors ============

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsCredentialsCompleteAttribute()
    {
        return $this->dmvLicense()->exists()
            && $this->backgroundCheck()->exists()
            && $this->dmvLicense->status_id == 1 // Verified
            && $this->backgroundCheck->status_id == 1; // Cleared
    }

    public function getStatusNameAttribute()
    {
        return match ($this->status_id) {
            1 => 'Active',
            2 => 'Inactive',
            3 => 'Suspended',
            4 => 'Pending Approval',
            default => 'Unknown'
        };
    }

    // ============ Scopes ============

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status_id', 1);
    }

    public function scopeVerified($query)
    {
        return $query->where('credentials_status_id', 1);
    }

    // ============ Methods ============

    public function updateLocation($lat, $lng, $speed = null, $heading = null)
    {
        $this->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'current_speed' => $speed,
            'current_heading' => $heading,
            'location_updated_at' => now(),
            'last_active_at' => now(),
        ]);
    }

    public function goOnline()
    {
        $this->update([
            'is_online' => true,
            'last_active_at' => now(),
        ]);
    }

    public function goOffline()
    {
        $this->update([
            'is_online' => false,
            'is_available' => true, // Reset availability
        ]);
    }

    public function markUnavailable()
    {
        $this->update(['is_available' => false]);
    }

    public function markAvailable()
    {
        $this->update(['is_available' => true]);
    }

    // public function ve
}
