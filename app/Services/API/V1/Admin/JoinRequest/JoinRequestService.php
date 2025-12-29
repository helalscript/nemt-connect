<?php

namespace App\Services\API\V1\Admin\JoinRequest;

use App\Models\DriverProfile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JoinRequestService
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
    public function index($request)
    {
        try {
            $per_page = $request->per_page ?? 10;
            $driver = DriverProfile::with(['user' => function ($q) {
                $q->select('id', 'name', 'email', 'phone');
            }, 'dmvLicense', 'extraLicenses', 'backgroundCheck', 'mtaDrugScreen', 'certifications', 'currentVehicle', 'vehicleAssignments' => function ($q) {
                with(['dmvRegistration', 'extraLicenses', 'insurance', 'inspection']);
            }, 'trips', 'shifts', 'ratings'])
                ->whereRelation('user', 'phone_verified_at', '!=', null)
                ->paginate($per_page);

            return $driver;

        } catch (Exception $e) {
            Log::error('JoinRequestService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new resource.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            //

        } catch (Exception $e) {
            Log::error('JoinRequestService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $driver = DriverProfile::with(['user' => function ($q) {
                $q->select('id', 'name', 'email', 'phone')->where('phone_verified_at', '!=', null);
            }, 'dmvLicense', 'extraLicenses', 'backgroundCheck', 'mtaDrugScreen', 'certifications', 'currentVehicle', 'vehicleAssignments' => function ($q) {
                $q->with(['vehicle' => function ($q) {
                    $q->with(['dmvRegistration', 'extraLicenses', 'insurance', 'inspection']);
                }])->get();
            }, 'trips', 'shifts', 'ratings'])->find($id);

            return $driver;
        } catch (Exception $e) {
            Log::error('JoinRequestService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {

        } catch (Exception $e) {
            Log::error('JoinRequestService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            // Logic to delete a specific resource
        } catch (Exception $e) {
            Log::error('JoinRequestService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
