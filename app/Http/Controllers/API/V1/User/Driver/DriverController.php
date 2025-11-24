<?php

namespace App\Http\Controllers\API\V1\User\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\Driver\DriverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{

    public function __construct(private DriverService $driverService) {}
    /**
     * Display a listing of the resource.
     */
    public function driverProfile()
    {
        try {
            $data = $this->driverService->driverProfile();
            return Helper::jsonResponse(true, 'Driver profile fetched successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('DriverController::myProfile'.$e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
    public function updateDriverProfile(Request $request)
    {
        try {
            $data = $this->driverService->updateProfile($request->all());
            return Helper::jsonResponse(true, 'Driver profile updated successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('DriverController::updateProfile'.$e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
