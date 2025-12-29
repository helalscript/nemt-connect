<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDmvLicense extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'license_number',
        'state_code',
        'license_class',
        'endorsements',
        'restrictions',
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
        'endorsements'    => 'array',
        'restrictions'    => 'array',
        'effective_date'  => 'date',
        'expiration_date' => 'date',
        'is_verified'     => 'boolean',
        'verified_at'     => 'datetime',
    ];
    
    public function driver()
    {
        return $this->belongsTo(DriverProfile::class);
    }
    
    public function getIsExpiredAttribute()
    {
        return $this->expiration_date < today();
    }
    
    public function getDaysUntilExpiryAttribute()
    {
        return today()->diffInDays($this->expiration_date, false);
    }
    public function getDocumentUrlAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }
}
