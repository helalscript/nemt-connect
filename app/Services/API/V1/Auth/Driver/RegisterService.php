<?php

namespace App\Services\API\V1\Auth\Driver;

use App\Helpers\Helper;
use App\Models\DriverBackgroundCheck;
use App\Models\DriverDmvLicense;
use App\Models\DriverExtraLicense;
use App\Models\DriverProfile;
use App\Models\DriverVehicleAssignment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDmvRegistration;
use App\Models\VehicleExtraLicense;
use App\Models\VehicleInspection;
use App\Models\VehicleInsurance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterService
{
    /**
     * Handle driver registration flow.
     */
    public function register(array $validatedData, Request $request): JsonResponse
    {
        try {
            $existingUser = User::withTrashed()
                ->where(function ($query) use ($validatedData) {
                    $query->where('email', data_get($validatedData, 'email'));
                    if (! empty(data_get($validatedData, 'phone'))) {
                        $query->orWhere('phone', data_get($validatedData, 'phone'));
                    }
                })
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                $existingUser->restore();
                $existingUser->update([
                    'email' => data_get($validatedData, 'email'),
                    'first_name' => data_get($validatedData, 'first_name'),
                    'last_name' => data_get($validatedData, 'last_name'),
                    'phone' => data_get($validatedData, 'phone'),
                    'password' => Hash::make(rand(100000, 999999)),
                    'role' => 'driver',
                ]);
                $user = $existingUser;
            } else {
                $userData = [
                    'email' => data_get($validatedData, 'email'),
                    'first_name' => data_get($validatedData, 'first_name'),
                    'last_name' => data_get($validatedData, 'last_name'),
                    'phone' => data_get($validatedData, 'phone'),
                    'password' => Hash::make(rand(100000, 999999)),
                    'gender' => data_get($validatedData, 'gender'),
                    'birth_date' => data_get($validatedData, 'birth_date'),
                    'role' => 'driver',
                ];
            }

            DB::beginTransaction();

            if (! isset($user)) {
                $user = User::create($userData);

                $driverProfile = $this->createDriverProfile($user->id, $validatedData);
                $this->createDriverDmvLicense($driverProfile, $request, $validatedData);
                $this->createDriverExtraLicense($driverProfile, $request, $validatedData);
                $this->createDriverBackgroundCheck($driverProfile, $request, $validatedData);

                $vehicle = $this->createVehicle($driverProfile, $validatedData);
                $this->createVehicleDmvRegistration($vehicle, $validatedData);
                $this->createVehicleExtraLicense($vehicle, $validatedData);
                $this->createVehicleInsurance($vehicle, $validatedData);
                $this->createVehicleInspection($vehicle, $validatedData);
                $this->createDriverVehicleAssignment($driverProfile, $vehicle);
            }

            $this->sendOtpToPhoneOrEmail($user, 'phone');

            DB::commit();

            return Helper::jsonResponse(true, 'A verification email has been successfully sent to your email address. Please check your inbox to complete the verification.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RegisterService::register'.$e->getMessage());

            return Helper::jsonErrorResponse('Failed to send verification email. Please try again later.', 500);
        }
    }

    private function sendOtpToPhoneOrEmail($user, $type = 'phone')
    {
        $otp = 1234; // For testing purposes, you can comment this line. In production, uncomment it.
        $otpExpiresAt = Carbon::now()->addMinutes(60);

        if ($type === 'email') {
            try {
                // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Dont share this OTP with anyone.'));
                $user->otp = $otp;
                $user->otp_expires_at = $otpExpiresAt;
                $user->save();

                return true;
            } catch (Exception $e) {
                Log::error('Email OTP send failed: '.$e->getMessage());
                throw new Exception('Failed to send OTP: '.$e->getMessage());
            }
        }

        if ($type === 'phone') {
            try {
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

    private function mapGender(?string $gender): ?string
    {
        return match ($gender) {
            'male' => 'M',
            'female' => 'F',
            'others' => 'O',
            default => null,
        };
    }

    private function createDriverProfile(int $userId, array $data): DriverProfile
    {
        return DriverProfile::create([
            'user_id' => $userId,
            'first_name' => data_get($data, 'first_name'),
            'last_name' => data_get($data, 'last_name'),
            'phone' => data_get($data, 'phone'),
            'social_security_number' => data_get($data, 'social_security_number'),
            'gender' => $this->mapGender(data_get($data, 'gender')),
            'birth_date' => data_get($data, 'birth_date'),
            'working_area_name' => data_get($data, 'working_area'),
            'working_area_lat' => data_get($data, 'working_area_lat'),
            'working_area_lng' => data_get($data, 'working_area_lng'),
            'working_area_radius' => data_get($data, 'working_area_radius'),
        ]);
    }

    private function createDriverDmvLicense(DriverProfile $driverProfile, Request $request, array $data): DriverDmvLicense
    {
        $documentUrl = $this->uploadDocument(
            $request,
            'dmv_license.document',
            'dmv_licenses',
            data_get($data, 'dmv_license.license_number', 'dmv_license')
        );

        return DriverDmvLicense::create([
            'driver_profile_id' => $driverProfile->id,
            'license_number' => data_get($data, 'dmv_license.license_number'),
            'state_code' => data_get($data, 'dmv_license.state_code'),
            'license_class' => data_get($data, 'dmv_license.license_class'),
            'endorsements' => data_get($data, 'dmv_license.endorsements'),
            'restrictions' => data_get($data, 'dmv_license.restrictions'),
            'expiration_date' => data_get($data, 'dmv_license.expiration_date'),
            'effective_date' => data_get($data, 'dmv_license.effective_date'),
            'document_url' => $documentUrl,
        ]);
    }

    private function createDriverExtraLicense(DriverProfile $driverProfile, Request $request, array $data): ?DriverExtraLicense
    {
        if (empty(data_get($data, 'extra_commercial_licenses'))) {
            return null;
        }

        $documentUrl = $this->uploadDocument(
            $request,
            'extra_commercial_licenses.document',
            'extra_commercial_licenses',
            data_get($data, 'extra_commercial_licenses.license_number', 'extra_commercial_license')
        );

        return DriverExtraLicense::create([
            'driver_profile_id' => $driverProfile->id,
            'license_number' => data_get($data, 'extra_commercial_licenses.license_number'),
            'type_id' => data_get($data, 'extra_commercial_licenses.type_id'),
            'effective_date' => data_get($data, 'extra_commercial_licenses.effective_date'),
            'expiration_date' => data_get($data, 'extra_commercial_licenses.expiration_date'),
            'document_url' => $documentUrl,
        ]);
    }

    private function createDriverBackgroundCheck(DriverProfile $driverProfile, Request $request, array $data): ?DriverBackgroundCheck
    {
        if (empty(data_get($data, 'background_check'))) {
            return null;
        }

        $documentUrl = $this->uploadDocument(
            $request,
            'background_check.document',
            'background_checks',
            data_get($data, 'background_check.license_number', 'background_check')
        );

        return DriverBackgroundCheck::create([
            'driver_profile_id' => $driverProfile->id,
            'document_url' => $documentUrl,
            'effective_date' => data_get($data, 'background_check.effective_date'),
            'expiration_date' => data_get($data, 'background_check.expiration_date'),
        ]);
    }

    private function createVehicle(DriverProfile $driverProfile, array $data): Vehicle
    {
        return Vehicle::create([
            'vin' => data_get($data, 'vin'),
            'fleet_number' => data_get($data, 'fleet_number'),
            'production_year' => data_get($data, 'production_year'),
            'type_id' => data_get($data, 'type_id'),
            'make' => data_get($data, 'make'),
            'model' => data_get($data, 'model'),
            'color' => data_get($data, 'color'),
            'seat_number' => data_get($data, 'seat_number'),
            'vehicle_status_id' => data_get($data, 'vehicle_status_id'),
        ]);
    }

    private function createVehicleDmvRegistration(Vehicle $vehicle, array $data): VehicleDmvRegistration
    {
        return VehicleDmvRegistration::create([
            'vehicle_id' => $vehicle->id,
            'license_plate_number' => data_get($data, 'dmv_registration.license_plate_number'),
            'license_plate_category_id' => data_get($data, 'dmv_registration.license_plate_category_id'),
            'state_code' => data_get($data, 'dmv_registration.state_code'),
            'effective_date' => data_get($data, 'dmv_registration.effective_date'),
            'expiration_date' => data_get($data, 'dmv_registration.expiration_date'),
        ]);
    }

    private function createVehicleExtraLicense(Vehicle $vehicle, array $data): ?VehicleExtraLicense
    {
        if (empty(data_get($data, 'extra_commercial_license'))) {
            return null;
        }

        return VehicleExtraLicense::create([
            'vehicle_id' => $vehicle->id,
            'license_number' => data_get($data, 'extra_commercial_license.license_number'),
            'type_id' => data_get($data, 'extra_commercial_license.type_id'),
            'effective_date' => data_get($data, 'extra_commercial_license.effective_date'),
            'expiration_date' => data_get($data, 'extra_commercial_license.expiration_date'),
        ]);
    }

    private function createVehicleInsurance(Vehicle $vehicle, array $data): VehicleInsurance
    {
        return VehicleInsurance::create([
            'vehicle_id' => $vehicle->id,
            'policy_number' => data_get($data, 'insurance.policy_number'),
            'insurer_name' => data_get($data, 'insurance.insurer_name'),
            'document' => data_get($data, 'insurance.document'),
            'effective_date' => data_get($data, 'insurance.effective_date'),
            'expiration_date' => data_get($data, 'insurance.expiration_date'),
        ]);
    }

    private function createVehicleInspection(Vehicle $vehicle, array $data): VehicleInspection
    {
        return VehicleInspection::create([
            'vehicle_id' => $vehicle->id,
            'document' => data_get($data, 'inspection.document'),
            'effective_date' => data_get($data, 'inspection.effective_date'),
            'expiration_date' => data_get($data, 'inspection.expiration_date'),
        ]);
    }

    private function createDriverVehicleAssignment(DriverProfile $driverProfile, Vehicle $vehicle): DriverVehicleAssignment
    {
        return DriverVehicleAssignment::create([
            'driver_profile_id' => $driverProfile->id,
            'vehicle_id' => $vehicle->id,
            'is_primary' => true,
            'is_active' => true,
            'assigned_at' => now(),
        ]);
    }

    private function uploadDocument(Request $request, string $inputKey, string $folder, ?string $name = null): ?string
    {
        if (! $request->hasFile($inputKey)) {
            return null;
        }

        $file = $request->file($inputKey);
        if ($file === null) {
            return null;
        }

        return Helper::fileUpload($file, $folder, $name ?? $folder);
    }

    public function verifyPhone(array $validatedData)
    {
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

            return [
                'token_type' => 'bearer',
                'token' => $token,
                'data' => $user];
        } catch (Exception $e) {
            Log::error('RegisterService::resendOtp'.$e->getMessage());
            throw new Exception('Failed to send OTP. Please try again later.');
        }
    }

    public function resendOtp(array $validatedData)
    {
        try {
            $user = User::where('phone', $validatedData['phone'])->first();
            if (!$user) {
                throw new Exception('User not found.');
                }
            if ($user->phone_verified_at !== null) {
                throw new Exception('Phone already verified.');
            }
            $this->sendOtpToPhoneOrEmail($user, 'phone');

            return true;
        } catch (Exception $e) {
            Log::error('Driver:RegisterService::resendOtp'.$e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
