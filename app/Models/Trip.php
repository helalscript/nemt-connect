<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes,HasFactory;
    
    protected $fillable = [
        'sentry_trip_id',
        'date',
        // ... all fields from migration
        'custom_price',
        'custom_price_note',
        'custom_price_set_at',
        'custom_price_set_by'
    ];
    
    protected $casts = [
        'date'                               => 'date',
        'scheduled_pickup_time'              => 'datetime',
        'latest_pickup_time'                 => 'datetime',
        'scheduled_dropoff_time'             => 'datetime',
        'pickup_arrival_time'                => 'datetime',
        'actual_pickup_time'                 => 'datetime',
        'actual_dropoff_time'                => 'datetime',
        'assigned_at'                        => 'datetime',
        'cancelled_at'                       => 'datetime',
        'rejected_at'                        => 'datetime',
        'custom_price_set_at'                => 'datetime',
        'last_modified_at'                   => 'datetime',
        'signature_captured_at'              => 'datetime',
        'procedures'                         => 'array', // JSON to array
        'is_low_vehicle_required'            => 'boolean',
        'is_congestion_surcharge_route'      => 'boolean',
        'is_confirmed'                       => 'boolean',
        'is_covid_transport'                 => 'boolean',
        'is_done_by_not_integrated_provider' => 'boolean'
    ];
    
    // ============ Relationships ============
    
    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    public function customPriceSetByUser()
    {
        return $this->belongsTo(User::class, 'custom_price_set_by');
    }
    
    // ============ Accessors ============
    
    /**
     * Get final price (custom or sentry)
     */
    public function getFinalPriceAttribute()
    {
        return $this->custom_price ?? $this->sentry_actual_cost;
    }
    
    /**
     * Check if custom price is set
     */
    public function getHasCustomPriceAttribute()
    {
        return !is_null($this->custom_price);
    }
    
    /**
     * Get price difference
     */
    public function getPriceDifferenceAttribute()
    {
        if ($this->has_custom_price) {
            return $this->custom_price - $this->sentry_actual_cost;
        }
        return 0;
    }
    
    /**
     * Full pickup address
     */
    public function getFullPickupAddressAttribute()
    {
        $parts = array_filter([
            $this->pickup_address,
            $this->pickup_building,
            $this->pickup_apartment,
            $this->pickup_city,
            $this->pickup_state_code,
            $this->pickup_zip_code
        ]);
        
        return implode(', ', $parts);
    }
    
    /**
     * Full dropoff address
     */
    public function getFullDropoffAddressAttribute()
    {
        $parts = array_filter([
            $this->dropoff_address,
            $this->dropoff_building,
            $this->dropoff_apartment,
            $this->dropoff_city,
            $this->dropoff_state_code,
            $this->dropoff_zip_code
        ]);
        
        return implode(', ', $parts);
    }
    
    /**
     * Trip duration in minutes
     */
    public function getTripDurationAttribute()
    {
        if ($this->actual_pickup_time && $this->actual_dropoff_time) {
            return $this->actual_pickup_time->diffInMinutes($this->actual_dropoff_time);
        }
        return null;
    }
    
    // ============ Scopes ============
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending_assignment');
    }
    
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }
    
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            'assigned',
            'accepted',
            'en_route_to_pickup',
            'arrived_at_pickup',
            'patient_on_board',
            'en_route_to_dropoff',
            'arrived_at_dropoff'
        ]);
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today());
    }
    
    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }
}
