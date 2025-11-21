<?php

namespace App\Services\API\V1\User\Driver;

use App\Models\DriverProfile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DriverService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function driverProfile()
    {
        try {
            $driverProfile = DriverProfile::where('user_id', $this->user->id)
                ->with('dmvLicense', 'extraLicenses', 'backgroundCheck', 'mtaDrugScreen', 'certifications', 'currentVehicle')
                ->first();
            if (! $driverProfile) {
                throw new Exception('Driver profile not found');
            }

            return $driverProfile;
        } catch (Exception $e) {
            Log::error('DriverService::myProfile'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new resource.
     *
     * @return mixed
     */
    public function updateProfile(array $validatedData)
    {
        try {

        } catch (Exception $e) {
            Log::error('DriverService::store'.$e->getMessage());
            throw $e;
        }
    }
}
