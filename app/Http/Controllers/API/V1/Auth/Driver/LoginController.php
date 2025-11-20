<?php

namespace App\Http\Controllers\API\V1\Auth\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Auth\Driver\LoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __construct(private LoginService $loginService) {}

    /**
     * Logs in the user.
     *
     * @bodyParam email string required The email of the user.
     * @bodyParam password string required The password of the user.
     * @bodyParam role string required The role of the user. Can be either customer or contractor.
     */
    public function login(Request $request)
    {

        // return $next($request);
        $validateData = $request->validate([
            'phone' => [
                'required',
                'string',
                'required_without:email',
                'regex:/^(\+?\d{1,4}[\s-]?)?(\(?\d{1,3}\)?[\s-]?)?[\d\s-]{7,15}$/',
            ],
        ]);
        try {

            $data = $this->loginService->login($validateData);

            return Helper::jsonResponse(true, 'OTP sent successfully to your phone number.', 200);
        } catch (Exception $e) {
            Log::error('Driver::LoginController::Login'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function verifyLoginOtp(Request $request)
    {
        $validateData = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|digits:4',
        ]);
        try {
            $data = $this->loginService->verifyLoginOtp($validateData);
            return Helper::jsonResponse(true, 'OTP verified successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('Driver::LoginController::verifyOtp ' . $e->getMessage());
            return Helper::jsonErrorResponse('Something went wrong. Please try again later. ' . $e->getMessage(), 500);
        }
    }

 
}
