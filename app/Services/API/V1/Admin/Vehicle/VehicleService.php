<?php

namespace App\Services\API\V1\Admin\Vehicle;

use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VehicleService
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
            $vehicles = Vehicle::with(['dmvRegistration','extraLicenses','insurances','inspections'])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page, $pageCount);

            return $vehicles;
            
        } catch (Exception $e) {
         Log::error("VehicleService::index" . $e->getMessage());
            throw $e;
             
        }
    }

    /**
     * Store a new resource.
     *
     * @param array $validatedData
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            
        } catch (Exception $e) {
         Log::error("VehicleService::store" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            
        } catch (Exception $e) {
         Log::error("VehicleService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @param int $id
     * @param array $validatedData
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            
        } catch (Exception $e) {
         Log::error("VehicleService::update" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            // Logic to delete a specific resource
        } catch (Exception $e) {
         Log::error("VehicleService::destroy" . $e->getMessage());
            throw $e;
        }
    }

}