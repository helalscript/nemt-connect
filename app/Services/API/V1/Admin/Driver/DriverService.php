<?php

namespace App\Services\API\V1\Admin\Driver;

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
    public function index($request)
    {
        try {
            $perPage   = $request->per_page ?? 10;
            $page      = $request->page ?? 1;
            $pageCount = $perPage * $page;
            $search    = $request->search ?? '';
            $drivers = DriverProfile::with(['user','dmvLicense','extraLicenses','backgroundCheck'])
                ->whereRelation('user', 'phone_verified_at', '!=', null)
                ->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                })
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page, $pageCount);

            return $drivers;
        } catch (Exception $e) {
            Log::error('DriverService::index'.$e->getMessage());
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

        } catch (Exception $e) {
            Log::error('DriverService::store'.$e->getMessage());
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
                $q->select('id', 'name', 'email', 'phone');
            }, 'currentVehicle'])
                ->whereRelation('user', 'phone_verified_at', '!=', null)
                ->find($id);

            if (! $driver) {
                throw new Exception('Driver not found');
            }

            return $driver;
        } catch (Exception $e) {
            Log::error('DriverService::show'.$e->getMessage());
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
        //
    }

    /**
     * Delete a specific resource.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $driver = DriverProfile::where('id', $id)->whereRelation('user', 'phone_verified_at', '!=', null)->first();
            if (! $driver) {
                throw new Exception('Driver not found');
            }
            $driver->delete();

            return $driver;
        } catch (Exception $e) {
            Log::error('DriverService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
