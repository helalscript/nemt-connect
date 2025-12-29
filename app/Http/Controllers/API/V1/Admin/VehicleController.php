<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\Vehicle\VehicleService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Exception;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function __construct(protected VehicleService $vehicleService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $vehicles = $this->vehicleService->index($request);

            return Helper::jsonResponse(true, 'Vehicles fetched successfully', 200, $vehicles);
        } catch (Exception $e) {
            Log::error('VehicleController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $vehicle = $this->vehicleService->store($request->all());

            return Helper::jsonResponse(true, 'Vehicle created successfully', 200, $vehicle);
        } catch (Exception $e) {
            Log::error('VehicleController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $vehicle = $this->vehicleService->show($id);

            return Helper::jsonResponse(true, 'Vehicle fetched successfully', 200, $vehicle);
        } catch (Exception $e) {
            Log::error('VehicleController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = $this->vehicleService->update($id, $request->all());

            return Helper::jsonResponse(true, 'Vehicle updated successfully', 200, $vehicle);
        } catch (Exception $e) {
            Log::error('VehicleController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $vehicle = $this->vehicleService->destroy($id);

            return Helper::jsonResponse(true, 'Vehicle deleted successfully', 200, $vehicle);
        } catch (Exception $e) {
            Log::error('VehicleController::destroy'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
