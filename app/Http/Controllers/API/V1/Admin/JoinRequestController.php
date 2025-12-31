<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\JoinRequest\JoinRequestService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JoinRequestController extends Controller
{
    public function __construct(protected JoinRequestService $joinRequestService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $joinRequests = $this->joinRequestService->index($request);

            return Helper::jsonResponse(true, 'Join requests fetched successfully', 200, $joinRequests,true);
        } catch (Exception $e) {
            Log::error('JoinRequestController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
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
        try {
            $joinRequest = $this->joinRequestService->show($id);

            return Helper::jsonResponse(true, 'Join request fetched successfully', 200, $joinRequest);
        } catch (Exception $e) {
            Log::error('JoinRequestController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
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

    public function accept(string $id)
    {
        try {
            $joinRequest = $this->joinRequestService->accept($id);
            return Helper::jsonResponse(true, 'Join request accepted successfully', 200, $joinRequest);
        } catch (Exception $e) {
            Log::error('JoinRequestController::accept'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function reject(string $id, Request $request)
    {
        $validatedData = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        try {
            $joinRequest = $this->joinRequestService->reject($id, $validatedData);
            return Helper::jsonResponse(true, 'Join request rejected successfully', 200, $joinRequest);
        } catch (Exception $e) {
            Log::error('JoinRequestController::reject'.$e->getMessage());
 
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
