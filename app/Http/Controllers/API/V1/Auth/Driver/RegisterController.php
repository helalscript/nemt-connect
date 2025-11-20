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
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function __construct(private RegisterService $registerService) {}

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
    public function verifyPhone(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|numeric|max_digits:20|exists:users,phone',
            'otp' => 'required|digits:4',
        ]);
        try {
            $data = $this->registerService->verifyPhone($validatedData);

            return Helper::jsonResponse(true, 'Phone verified successfully.', 200, $data);
        } catch (Exception $e) {
            Log::error('RegisterController::VerifyPhone'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 403);
        }
    }

    /**
     * Resend OTP to the user's email address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|numeric|max_digits:20|exists:users,phone',
        ]);
        try {
            $data = $this->registerService->resendOtp($validatedData);

            return Helper::jsonResponse(true, 'OTP sent successfully.', 200, $data);

        } catch (Exception $e) {
            Log::error('RegisterController::ResendOtp'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 403);
        }
    }
}
