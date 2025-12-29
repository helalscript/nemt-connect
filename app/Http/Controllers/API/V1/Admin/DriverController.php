<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\Driver\DriverService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Exception;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function __construct(protected DriverService $driverService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $drivers = $this->driverService->index($request);

            return Helper::jsonResponse(true, 'Drivers fetched successfully', 200, $drivers, true);
        } catch (Exception $e) {
            Log::error('DriverController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $driver = $this->driverService->store($request->all());

            return Helper::jsonResponse(true, 'Driver created successfully', 200, $driver);
        } catch (Exception $e) {
            Log::error('DriverController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $driver = $this->driverService->show($id);

            return Helper::jsonResponse(true, 'Driver fetched successfully', 200, $driver);
        } catch (Exception $e) {
            Log::error('DriverController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $driver = $this->driverService->update($id, $request->all());

            return Helper::jsonResponse(true, 'Driver updated successfully', 200, $driver);
        } catch (Exception $e) {
            Log::error('DriverController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $driver = $this->driverService->destroy($id);

            return Helper::jsonResponse(true, 'Driver deleted successfully', 200, $driver);
        } catch (Exception $e) {
            Log::error('DriverController::destroy'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
