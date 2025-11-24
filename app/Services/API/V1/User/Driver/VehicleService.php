<?php

namespace App\Services\API\V1\User\Driver;

use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VehicleService
{
    protected $user;

    protected $driverProfile;

    public function __construct()
    {
        $this->user = Auth::user()->load('driverProfile');
        $this->driverProfile = $this->user->driverProfile;
    }

    public function driverVehicles()
    {
        try {
            $cacheKey = 'driver_vehicles_'.$this->driverProfile->id;

            $vehicles = Cache::remember($cacheKey, now()->addMinutes(10), function () {
                return Vehicle::where('owner_driver_profile_id', $this->driverProfile->id)
                ->with('dmvRegistration', 'extraLicenses', 'insurance', 'inspection')
                ->get();
            });

            return $vehicles;

        } catch (Exception $e) {
            Log::error('VehicleService::driverVehicles'.$e->getMessage());
            throw $e;
        }
    }

    public function updateDriverVehicle(array $validatedData)
    {
        try {
            $vehicle = Vehicle::where('id', $validatedData['id'])->where('owner_driver_profile_id', $this->driverProfile->id)->first();
            if (! $vehicle) {
                throw new Exception('Vehicle not found');
            }
            $vehicle->update($validatedData);

            Cache::forget('driver_vehicles_'.$this->driverProfile->id);

            return $vehicle;
        } catch (Exception $e) {
            Log::error('VehicleService::updateDriverVehicle'.$e->getMessage());
            throw $e;
        }
    }
}
