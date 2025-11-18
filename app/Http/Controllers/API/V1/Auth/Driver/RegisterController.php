<?php

namespace App\Http\Controllers\API\V1\Auth\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\License;
use App\Models\User;
use App\Notifications\UserRegistrationNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // dd($request->all());
        $validateData = $request->validate(
            [
                //======== this for extra fields for my system =======
                'working_area' => 'required|string|max:255',
                'working_area_lat' => 'required|numeric',
                'working_area_lng' => 'required|numeric',
                'working_area_radius' => 'required|integer|min:1|max:100',

                //======== this for syntry system fields ========
                // Driver profile
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'social_security_number' => 'required|string|max:50|unique:driver_profiles,social_security_number,NULL,id,deleted_at,NULL',
                'gender' => 'required|string|in:male,female,others', //M,F,O
                'birth_date' => 'required|date|before_or_equal:today',
                'phone' => 'required|numeric|max_digits:20|unique:users,phone,NULL,id,deleted_at,NULL',
                // 'driver_status_id' => 'required|integer|in:1,2', // status_id 1 => active, 2 => not active

                // DMV license nested validation
                'dmv_license' => 'required|array',
                'dmv_license.license_number' => 'required|string|max:20',
                'dmv_license.state_code' => 'required|string|size:2',
                'dmv_license.license_class' => 'required|string|max:5',
                'dmv_license.endorsements' => 'array',
                'dmv_license.endorsements.*' => 'string|max:5',
                'dmv_license.restrictions' => 'array',
                'dmv_license.restrictions.*' => 'string|max:5',
                'dmv_license.document' => 'nullable|file|mimes:pdf|max:5120',
                // 'dmv_license.document_url' => 'nullable|url',
                'dmv_license.effective_date' => 'required|date',
                'dmv_license.expiration_date' => 'required|date|after_or_equal:dmv_license.effective_date',

                // Extra commercial licenses
                'extra_commercial_licenses' => 'array',
                'extra_commercial_licenses.license_number' => 'required|string|max:20',
                'extra_commercial_licenses.type_id' => 'required|integer|in:1,2,3,4,5,6',
                'extra_commercial_licenses.document' => 'nullable|file|mimes:pdf|max:5120',
                // 'extra_commercial_licenses.document_url' => 'nullable|url',
                'extra_commercial_licenses.effective_date' => 'required|date',
                'extra_commercial_licenses.expiration_date' => 'required|date',
                'after_or_equal:extra_commercial_licenses.effective_date',

                // Background check
                'background_check' => 'required|array',
                'background_check.document' => 'nullable|file|mimes:pdf|max:5120',
                // 'background_check.document_url' => 'nullable|url',
                'background_check.effective_date' => 'required|date',
                'background_check.expiration_date' => 'required|date|after_or_equal:background_check.effective_date',

                //vehicle info
                "vin" => "required|string|max:50",
                "fleet_number" => "nullable|string|max:50",
                "production_year" => "required|integer|min:1900|max:" . date('Y'),
                "type_id" => "required|integer",
                "make" => "required|string|max:100",
                "model" => "required|string|max:100",
                "color" => "required|string|max:50",
                "seat_number" => "nullable|integer|min:1|max:100",
                "vehicle_status_id" => "required|integer", //status_id 1 => active, 2 => inactive 3=> suspended

                // DMV REGISTRATION (object)
                "dmv_registration" => "required|array",
                "dmv_registration.license_plate_number" => "required|string|max:20",
                "dmv_registration.license_plate_category_id" => "required|integer",
                "dmv_registration.state_code" => "required|string|size:2",
                "dmv_registration.document" => "nullable|file|mimes:pdf|max:5120",
                // "dmv_registration.document_url" => "nullable|url",
                "dmv_registration.effective_date" => "required|date",
                "dmv_registration.expiration_date" => "required|date|after_or_equal:dmv_registration.effective_date",

                // EXTRA COMMERCIAL LICENSE (object)
                "extra_commercial_license" => "nullable|array",
                "extra_commercial_license.license_number" => "required_with:extra_commercial_license|string|max:50",
                "extra_commercial_license.type_id" => "required_with:extra_commercial_license|integer",
                "extra_commercial_license.document" => "nullable|file|mimes:pdf|max:5120",
                // "extra_commercial_license.document_url" => "nullable|url",
                "extra_commercial_license.effective_date" => "required_with:extra_commercial_license|date",
                "extra_commercial_license.expiration_date" => "required_with:extra_commercial_license|date|after_or_equal:extra_commercial_license.effective_date",

                // INSURANCE (object)
                "insurance" => "required|array",
                "insurance.policy_number" => "required|string|max:100",
                "insurance.insurer_name" => "required|string|max:150",
                "insurance.document" => "nullable|file|mimes:pdf|max:5120",
                // "insurance.document_url" => "nullable|url",
                "insurance.effective_date" => "required|date",
                "insurance.expiration_date" => "required|date|after_or_equal:insurance.effective_date",

                // INSPECTION (object)
                "inspection" => "required|array",
                "inspection.document" => "nullable|file|mimes:pdf|max:5120",
                // "inspection.document_url" => "nullable|url",
                "inspection.effective_date" => "required|date",
                "inspection.expiration_date" => "required|date|after_or_equal:inspection.effective_date",
            ],
            [
                // 'gender.in' => 'Gender must be M, F, or O.',
                'dmv_license.expiration_date.after_or_equal' => 'DMV license expiration date must be after or equal to effective date.',
                'extra_commercial_licenses.*.expiration_date.after_or_equal' => 'Extra license expiration date must be after or equal to effective date.',
                'background_check.expiration_date.after_or_equal' => 'Background check expiration date must be after or equal to effective date.',
            ]
        );
        dd($validateData);
        try {
            $otp = 1234; // For testing purposes, you can use a fixed OTP. In production, generate a random OTP.
            // $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(60); // 1 hour

            // Check for soft-deleted user
            $existingUser = User::withTrashed()
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->input('email'));
                    if ($request->filled('phone')) {
                        $query->orWhere('phone', $request->input('phone'));
                    }
                })
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft-deleted user
                $existingUser->restore();
                $existingUser->update([
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    // 'email' => $request->input('email')?? null,
                    'phone' => $request->input('phone') ?? null,
                    'password' => Hash::make(rand(100000, 999999)),
                    // 'password' => Hash::make($request->input('password')),
                    'role' => 'driver', // Default role
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ]);
                $user = $existingUser;
            } else {
                $userData = [
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    // 'email' => $request->input('email'), --- IGNORE ---
                    'phone' => $request->input('phone') ?? null,
                    'password' => Hash::make(rand(100000, 999999)),
                    // 'password' => Hash::make($request->input('password')),
                    'gender' => $request->input('gender'),
                    'birth_date' => $request->input('birth_date'),
                    'role' => 'driver', // Default role
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ];
                //create driver profile
                $driverProfile = DriverProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    'social_security_number' => $request->input('social_security_number'),
                    'gender' => $request->input('gender'),
                    'birth_date' => $request->input('birth_date'),
                    'working_area_name' => $request->input('working_area'),
                    'working_area_lat' => $request->input('working_area_lat'),
                    'working_area_lng' => $request->input('working_area_lng'),
                    'working_area_radius' => $request->input('working_area_radius'),
                ]);
                //create dmv license
                $dmvLicense = DriverDmvLicense::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_number' => $request->input('dmv_license.license_number'),
                    'state_code' => $request->input('dmv_license.state_code'),
                    'license_class' => $request->input('dmv_license.license_class'),
                    'endorsements' => $request->input('dmv_license.endorsements'),
                    'restrictions' => $request->input('dmv_license.restrictions'),
                ]);
                //create extra commercial licenses
                $extraCommercialLicenses = DriverExtraLicense::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_number' => $request->input('extra_commercial_licenses.license_number'),
                    'type_id' => $request->input('extra_commercial_licenses.type_id'),
                ]);
                //create background check
                $backgroundCheck = DriverBackgroundCheck::create([
                    'driver_profile_id' => $driverProfile->id,
                    'document' => $request->input('background_check.document'),
                    'effective_date' => $request->input('background_check.effective_date'),
                    'expiration_date' => $request->input('background_check.expiration_date'),
                ]);
                //create vehicle info
                $vehicle = Vehicle::create([
                    'driver_profile_id' => $driverProfile->id,
                    'vin' => $request->input('vin'),
                    'fleet_number' => $request->input('fleet_number'),
                    'production_year' => $request->input('production_year'),
                    'type_id' => $request->input('type_id'),
                    'make' => $request->input('make'),
                    'model' => $request->input('model'),
                    'color' => $request->input('color'),
                    'seat_number' => $request->input('seat_number'),
                    'vehicle_status_id' => $request->input('vehicle_status_id'),
                ]);
                //create dmv registration
                $dmvRegistration = DriverDmvRegistration::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_plate_number' => $request->input('dmv_registration.license_plate_number'),
                    'license_plate_category_id' => $request->input('dmv_registration.license_plate_category_id'),
                    'state_code' => $request->input('dmv_registration.state_code'),
                ]);
                //create extra commercial license
                $extraCommercialLicense = DriverExtraLicense::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_number' => $request->input('extra_commercial_license.license_number'),
                    'type_id' => $request->input('extra_commercial_license.type_id'),
                ]);
                //create insurance
                $insurance = DriverInsurance::create([
                    'driver_profile_id' => $driverProfile->id,
                    'policy_number' => $request->input('insurance.policy_number'),
                    'insurer_name' => $request->input('insurance.insurer_name'),
                    'document' => $request->input('insurance.document'),
                    'effective_date' => $request->input('insurance.effective_date'),
                    'expiration_date' => $request->input('insurance.expiration_date'),
                ]);
                //create inspection
                $inspection = DriverInspection::create([
                    'driver_profile_id' => $driverProfile->id,
                    'document' => $request->input('inspection.document'),
                    'effective_date' => $request->input('inspection.effective_date'),
                    'expiration_date' => $request->input('inspection.expiration_date'),
                ]);
            }

            DB::beginTransaction();

            if (!isset($user)) {
                $user = User::create($userData);
            }

            // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address')); // For testing purposes, you can comment this line. In production, uncomment it.

            DB::commit();

            return Helper::jsonResponse(true, 'A verification email has been successfully sent to your email address. Please check your inbox to complete the verification.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RegisterController::register' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to send verification email. Please try again later.', 500);
        }
    }

    /**
     * Resend OTP to the user's email address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ResendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
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
            Log::error('RegisterController::ResendOtp' . $e->getMessage());

            return Helper::jsonErrorResponse('Something worng', 403);
        }
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
        $request->validate([
            'phone' => 'required|numeric|max_digits:20|exists:users,phone',
            'otp' => 'required|digits:4',
        ]);
        try {
            $user = User::where('phone', $request->input('phone'))->first();

            // ! Check if phone has already been verified
            if ($user->phone_verified_at !== null) {
                return Helper::jsonResponse(false, 'Your phone has already been verified. Please login to continue.', 409);
            }

            if ($user->otp !== $request->otp) {
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
            Log::error('RegisterController::VerifyPhone' . $e->getMessage());

            return Helper::jsonErrorResponse('An error occurred during phone verification.', 403);
        }
    }
}
