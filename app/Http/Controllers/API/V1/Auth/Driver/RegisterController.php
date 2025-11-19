<?php

namespace App\Http\Controllers\API\V1\Auth\Driver;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Driver\RegisterRequest;
use App\Mail\OtpMail;
use App\Models\DriverBackgroundCheck;
use App\Models\DriverDmvLicense;
use App\Models\DriverExtraLicense;
use App\Models\DriverProfile;
use App\Models\DriverVehicleAssignment;
use App\Models\License;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDmvRegistration;
use App\Models\VehicleExtraLicense;
use App\Models\VehicleInspection;
use App\Models\VehicleInsurance;
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
    public function register(RegisterRequest $request)
    {
        // dd($request->all());
        $validatedData = $request->validated();
        try {
            // Check for soft-deleted user
            $existingUser = User::withTrashed()
                ->where(function ($query) use ($validatedData) {
                    $query->where('email', $validatedData['email']);
                    if (! empty($validatedData['phone'])) {
                        $query->orWhere('phone', $validatedData['phone']);
                    }
                })
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft-deleted user
                $existingUser->restore();
                $existingUser->update([
                    'email' => $validatedData['email'],
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'phone' => $validatedData['phone'] ?? null,
                    'password' => Hash::make(rand(100000, 999999)),
                    'role' => 'driver', // Default role
                ]);
                $user = $existingUser;
            } else {
                $userData = [
                    'email' => $validatedData['email'],
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'phone' => $validatedData['phone'] ?? null,
                    'password' => Hash::make(rand(100000, 999999)),
                    'gender' => $validatedData['gender'],
                    'birth_date' => $validatedData['birth_date'],
                    'role' => 'driver', // Default role
                ];

            }

            DB::beginTransaction();

            if (! isset($user)) {
                $user = User::create($userData);

                // create driver profile
                $driverProfile = DriverProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'phone' => $validatedData['phone'],
                    'social_security_number' => $validatedData['social_security_number'],
                    'gender' => $validatedData['gender'] === 'male' ? 'M' : ($validatedData['gender'] === 'female' ? 'F' : 'O'),
                    'birth_date' => $validatedData['birth_date'],
                    'working_area_name' => $validatedData['working_area'],
                    'working_area_lat' => $validatedData['working_area_lat'],
                    'working_area_lng' => $validatedData['working_area_lng'],
                    'working_area_radius' => $validatedData['working_area_radius'],
                ]);

                // upload dmv license document
                $dmvLicenseDocument = null;
                if ($request->hasFile('dmv_license.document')) {
                    $dmvLicenseFile = $request->file('dmv_license.document');
                    if ($dmvLicenseFile !== null) {
                        $dmvLicenseDocument = Helper::fileUpload(
                            $dmvLicenseFile,
                            'dmv_licenses',
                            data_get($validatedData, 'dmv_license.license_number', 'dmv_license')
                        );
                    }
                }

                // create dmv license
                $dmvLicense = DriverDmvLicense::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_number' => data_get($validatedData, 'dmv_license.license_number'),
                    'state_code' => data_get($validatedData, 'dmv_license.state_code'),
                    'license_class' => data_get($validatedData, 'dmv_license.license_class'),
                    'endorsements' => data_get($validatedData, 'dmv_license.endorsements'),
                    'restrictions' => data_get($validatedData, 'dmv_license.restrictions'),
                    'expiration_date' => data_get($validatedData, 'dmv_license.expiration_date'),
                    'effective_date' => data_get($validatedData, 'dmv_license.effective_date'),
                    'document_url' => $dmvLicenseDocument,
                ]);

                // upload extra commercial licenses document
                if ($request->hasFile('extra_commercial_licenses.document')) {
                    $extraCommercialLicensesFile = $request->file('extra_commercial_licenses.document');
                    if ($extraCommercialLicensesFile !== null) {
                        $extraCommercialLicensesDocument = Helper::fileUpload(
                            $extraCommercialLicensesFile,
                            'extra_commercial_licenses',
                            data_get($validatedData, 'extra_commercial_licenses.license_number', 'extra_commercial_license')
                        );
                    }
                }
                // create extra commercial licenses
                $extraCommercialLicenses = DriverExtraLicense::create([
                    'driver_profile_id' => $driverProfile->id,
                    'license_number' => data_get($validatedData, 'extra_commercial_licenses.license_number'),
                    'type_id' => data_get($validatedData, 'extra_commercial_licenses.type_id'),
                    'effective_date' => data_get($validatedData, 'extra_commercial_licenses.effective_date'),
                    'expiration_date' => data_get($validatedData, 'extra_commercial_licenses.expiration_date'),
                    'document_url' => $extraCommercialLicensesDocument ?? null,
                ]);

                // upload background check document
                $backgroundCheckDocument = null;
                if ($request->hasFile('background_check.document')) {
                    $backgroundCheckFile = $request->file('background_check.document');
                    if ($backgroundCheckFile !== null) {
                        $backgroundCheckDocument = Helper::fileUpload(
                            $backgroundCheckFile,
                            'background_checks',
                            data_get($validatedData, 'background_check.license_number', 'background_check')
                        );
                    }
                }
                // create background check
                if (! empty($validatedData['background_check'])) {
                    $backgroundCheck = DriverBackgroundCheck::create([
                        'driver_profile_id' => $driverProfile->id,
                        'document_url' => $backgroundCheckDocument,
                        'effective_date' => data_get($validatedData, 'background_check.effective_date'),
                        'expiration_date' => data_get($validatedData, 'background_check.expiration_date'),
                    ]);
                }

                // create vehicle info
                $vehicle = Vehicle::create([
                    'owner_driver_id' => $driverProfile->id,
                    'vin' => $validatedData['vin'],
                    'fleet_number' => $validatedData['fleet_number'] ?? null,
                    'production_year' => $validatedData['production_year'],
                    'type_id' => $validatedData['type_id'],
                    'make' => $validatedData['make'],
                    'model' => $validatedData['model'],
                    'color' => $validatedData['color'],
                    'seat_number' => $validatedData['seat_number'] ?? null,
                    'vehicle_status_id' => $validatedData['vehicle_status_id'],
                ]);

                // create dmv registration
                $dmvRegistration = VehicleDmvRegistration::create([
                    'vehicle_id' => $vehicle->id,
                    'license_plate_number' => data_get($validatedData, 'dmv_registration.license_plate_number'),
                    'license_plate_category_id' => data_get($validatedData, 'dmv_registration.license_plate_category_id'),
                    'state_code' => data_get($validatedData, 'dmv_registration.state_code'),
                ]);

                // create extra commercial license
                if (! empty($validatedData['extra_commercial_license'])) {
                    $extraCommercialLicense = VehicleExtraLicense::create([
                        'vehicle_id' => $vehicle->id,
                        'license_number' => data_get($validatedData, 'extra_commercial_license.license_number'),
                        'type_id' => data_get($validatedData, 'extra_commercial_license.type_id'),
                    ]);
                }

                // create insurance
                $insurance = VehicleInsurance::create([
                    'vehicle_id' => $vehicle->id,
                    'policy_number' => data_get($validatedData, 'insurance.policy_number'),
                    'insurer_name' => data_get($validatedData, 'insurance.insurer_name'),
                    'document' => data_get($validatedData, 'insurance.document'),
                    'effective_date' => data_get($validatedData, 'insurance.effective_date'),
                    'expiration_date' => data_get($validatedData, 'insurance.expiration_date'),
                ]);

                // create inspection
                $inspection = VehicleInspection::create([
                    'vehicle_id' => $vehicle->id,
                    'document' => data_get($validatedData, 'inspection.document'),
                    'effective_date' => data_get($validatedData, 'inspection.effective_date'),
                    'expiration_date' => data_get($validatedData, 'inspection.expiration_date'),
                ]);

                // create driver vehicle assignment
                $driverVehicleAssignment = DriverVehicleAssignment::create([
                    'driver_profile_id' => $driverProfile->id,
                    'vehicle_id' => $vehicle->id,
                    'is_primary' => true,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);

            }

            // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address')); // For testing purposes, you can comment this line. In production, uncomment it.

            $this->sendOtpToPhoneOrEmail($user, 'phone');

            DB::commit();

            return Helper::jsonResponse(true, 'A verification email has been successfully sent to your email address. Please check your inbox to complete the verification.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RegisterController::register'.$e->getMessage());

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

    private function sendOtpToPhoneOrEmail($user, $type = 'phone')
    {
        $otp = 1234; // For development (use rand in production)
        $otpExpiresAt = Carbon::now()->addMinutes(60); // 1 hour
        // Generate a 4-digit OTP (with leading zeros if needed)
        // $otp = sprintf("%04d", rand(0, 9999));

        if ($type === 'email') {
            try {
                // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Dont share this OTP with anyone.'));
                $user->otp = $otp;
                $user->otp_expires_at = $otpExpiresAt;
                $user->save();

                return true;
            } catch (Exception $e) {
                Log::error('Email OTP send failed: '.$e->getMessage());
                // return Helper::jsonErrorResponse('Failed to send new OTP', 500);
                throw new Exception('Failed to send OTP: '.$e->getMessage());
            }
        }

        if ($type === 'phone') {
            // $sid = env('TWILIO_SID');
            // $token = env('TWILIO_AUTH_TOKEN');
            // $from = env('TWILIO_PHONE_NUMBER');

            // if (!$sid || !$token || !$from) {
            //     Log::error('Twilio environment variables are missing.');
            //     throw new Exception('SMS service are missing.');
            // }

            try {
                // $client = new Client($sid, $token);
                // $client->messages->create($user->phone, [
                //     'body' => "Your OTP is: $otp",
                //     'from' => $from,
                // ]);
                $user->otp = $otp;
                $user->otp_expires_at = $otpExpiresAt;
                $user->save();

                return true;
            } catch (Exception $e) {
                Log::error('Twilio OTP send failed: '.$e->getMessage());
                throw new Exception('Failed to send OTP: '.$e->getMessage());
            }
        }
        throw new Exception('Invalid type for OTP sending.');
    }
}
