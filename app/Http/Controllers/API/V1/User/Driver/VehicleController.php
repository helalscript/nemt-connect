<?php

namespace App\Http\Controllers\API\V1\User\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\Driver\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function __construct(private VehicleService $vehicleService) {}

    public function driverVehicles()
    {
        try {
            $data = $this->vehicleService->driverVehicles();

            return Helper::jsonResponse(true, 'Driver vehicles fetched successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('VehicleController::driverVehicles'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function updateDriverVehicle(Request $request)
    {
        try {
            $data = $this->vehicleService->updateDriverVehicle($request->all());

            return Helper::jsonResponse(true, 'Driver vehicle updated successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('VehicleController::updateDriverVehicle'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
