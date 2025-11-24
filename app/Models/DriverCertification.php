<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverCertification extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'certification_type',
        'certification_number',
        'issuing_organization',
        'document_url',
        'effective_date',
        'expiration_date',
    ];
    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
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
