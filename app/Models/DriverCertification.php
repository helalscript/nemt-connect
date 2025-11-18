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
}
