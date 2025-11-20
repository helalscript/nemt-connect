<?php

namespace App\Http\Controllers\API\V1\Auth\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Driver\DriverBackgroundCheckRequest;
use App\Http\Requests\Auth\Driver\DriverDmvLicenseRequest;
use App\Http\Requests\Auth\Driver\DriverExtraLicenseRequest;
use App\Http\Requests\Auth\Driver\DriverProfileRequest;
use App\Http\Requests\Auth\Driver\VehicleDmvRegistrationRequest;
use App\Http\Requests\Auth\Driver\VehicleExtraLicenseRequest;
use App\Http\Requests\Auth\Driver\VehicleInspectionRequest;
use App\Http\Requests\Auth\Driver\VehicleInsuranceRequest;
use App\Http\Requests\Auth\Driver\VehicleRequest;
use App\Models\User;
use App\Services\API\V1\Auth\Driver\RegisterService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function __construct(private RegisterService $registerService)
    {
    }

    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validatedData = $this->validateRegistrationPayload($request);
        return $this->registerService->register($validatedData, $request);
    }

    /**
     * Resend OTP to the user's email address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ResendOtp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $user = User::where('email', $validatedData['email'])->first();
            if (! $user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            // $newOtp = rand(1000, 9999);
            $newOtp = 1234; // For testing purposes, you can comment this line. In production, uncomment it.
            $otpExpiresAt = Carbon::now()->addMinutes(60);
            $user->otp = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            // * Send the new OTP to the user's email

            try {
                // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));
                // Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address')); // For testing purposes, you can comment this line. In production, uncomment it.
                return Helper::jsonResponse(true, 'A new OTP has been sent to your email.', 200);
            } catch (Exception $e) {
                // If email sending fails, delete the created user and return an error message
                // $user->delete();
                return Helper::jsonErrorResponse('Failed to send new OTP', 500);
            }

        } catch (Exception $e) {
            Log::error('RegisterController::ResendOtp'.$e->getMessage());

            return Helper::jsonErrorResponse('Something worng', 403);
        }
    }

    private function validateRegistrationPayload(Request $request): array
    {
        return array_merge(
            $this->validateFormRequest(DriverProfileRequest::class, $request),
            $this->validateFormRequest(DriverDmvLicenseRequest::class, $request),
            $this->validateFormRequest(DriverExtraLicenseRequest::class, $request),
            $this->validateFormRequest(DriverBackgroundCheckRequest::class, $request),
            $this->validateFormRequest(VehicleRequest::class, $request),
            $this->validateFormRequest(VehicleDmvRegistrationRequest::class, $request),
            $this->validateFormRequest(VehicleExtraLicenseRequest::class, $request),
            $this->validateFormRequest(VehicleInsuranceRequest::class, $request),
            $this->validateFormRequest(VehicleInspectionRequest::class, $request),
        );
    }

    private function validateFormRequest(string $formRequestClass, Request $request): array
    {
        /** @var FormRequest $formRequest */
        $formRequest = $formRequestClass::createFromBase($request);
        $formRequest->setContainer(app())->setRedirector(app(Redirector::class));
        $formRequest->validateResolved();

        return $formRequest->validated();
    }

    /**
     * Verify the user's phone number.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function VerifyPhone(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|numeric|max_digits:20|exists:users,phone',
            'otp' => 'required|digits:4',
        ]);
        try {
            $user = User::where('phone', $validatedData['phone'])->first();

            // ! Check if phone has already been verified
            if ($user->phone_verified_at !== null) {
                return Helper::jsonResponse(false, 'Your phone has already been verified. Please login to continue.', 409);
            }

            if ($user->otp !== $validatedData['otp']) {
                return Helper::jsonResponse(false, 'Invalid OTP. Please try again.', 422);
            }

            // * Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonResponse(false, 'OTP has expired. Please request a new OTP.', 422);
            }

            // * Verify the phone
            $user->phone_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Notify admins about the new registration
            // $admins = User::where('role', 'admin')->get();
            // foreach ($admins as $admin) {
            //     $admin->notify(new UserRegistrationNotification($user, "{$user->name} has joined the platform. Please review their details."));
            // }

            // Generate an access token for the user
            $token = auth('api')->login($user);

            return response()->json([
                'status' => true,
                'message' => 'Phone verified successfully.',
                'code' => 200,
                'token_type' => 'bearer',
                'token' => $token,
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('RegisterController::VerifyPhone'.$e->getMessage());

            return Helper::jsonErrorResponse('An error occurred during phone verification.', 403);
        }
    }

}
