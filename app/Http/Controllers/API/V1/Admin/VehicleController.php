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
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
