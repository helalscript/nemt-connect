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

    public function accept(int $id)
    {
        try {
            $joinRequest = DriverProfile::find($id);
            if (!$joinRequest) {
                throw new Exception('Join request not found');
            }
            //if already verified, throw an exception
            if ($joinRequest->is_admin_verified) {
                throw new Exception('Join request already verified by admin');
            }
            //if already rejected, throw an exception
            if ($joinRequest->is_admin_rejected) {
                throw new Exception('Join request already rejected by admin');
            }
            // ============ Update Join Request ============
            $joinRequest->update([
                'is_admin_verified' => true,
                'admin_verified_at' => now(),
                'admin_verified_by' => $this->user->id,
                'admin_verified_note' => null,
            ]);
            // // ============ Send Notification to Driver ============
            // $driver = $joinRequest->user;
            // $driver->notify(new JoinRequestAcceptedNotification($joinRequest));
            return $joinRequest;
        } catch (Exception $e) {
            Log::error('JoinRequestService::accept'.$e->getMessage());
            throw $e;
        }
    }

    public function reject(int $id, array $validatedData)
    {
        try {
            $joinRequest = DriverProfile::find($id);
            if (!$joinRequest) {
                throw new Exception('Join request already rejected or not found');
            }
            //if already rejected, throw an exception
            if ($joinRequest->is_admin_rejected) {
                throw new Exception('Join request already rejected by admin');
            }
            //if already verified, throw an exception
            if ($joinRequest->is_admin_verified) {
                throw new Exception('Join request already verified by admin');
            }
            // ============ Update Join Request ============
            $joinRequest->update([
                'is_admin_rejected' => true,
                'admin_rejected_at' => now(),
                'admin_rejected_by' => $this->user->id,
                'admin_rejected_reason' => $validatedData['reason'],
            ]); 
            // // ============ Send Notification to Driver ============
            // $driver = $joinRequest->user;
            // $driver->notify(new JoinRequestRejectedNotification($joinRequest));
            return $joinRequest;
        } catch (Exception $e) {
            Log::error('JoinRequestService::reject'.$e->getMessage());
            throw $e;
        }
    }
}
