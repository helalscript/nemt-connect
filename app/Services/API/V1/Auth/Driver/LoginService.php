<?php

namespace App\Services\API\V1\Auth\Driver;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class LoginService
{
    public function login(array $validatedData)
    {
        try {
            $user = User::where('phone', $validatedData['phone'])->where('role', 'driver')->first();
            if (! $user) {
                throw new Exception('No account found with this phone number.');
            }
            $this->sendOtpToPhoneOrEmail($user, 'phone');

            return true;
        } catch (Exception $e) {
            Log::error('Driver::LoginService::login'.$e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function verifyLoginOtp(array $validatedData)
    {
        try {
            $user = User::where('phone', $validatedData['phone'])->where('role', 'driver')->first();
            if (! $user) {
                throw new Exception('No account found with this phone number.');
            }
            if ($user->otp !== $validatedData['otp']) {
                throw new Exception('Invalid OTP.');
            }
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                throw new Exception('OTP has expired.');
            }

            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Login user and generate token
            $token = auth('api')->login($user);

            return [
                'token_type' => 'bearer',
                'token' => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user,
            ];
        } catch (Exception $e) {
            Log::error('Driver::LoginService::verifyOtp'.$e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function refreshToken()
    {
        try {
            $refreshToken = auth('api')->refresh();

            return [
                'token_type' => 'bearer',
                'token' => $refreshToken,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            Log::error('Driver::LoginService::refreshToken - Blacklisted: '.$e->getMessage());
            throw new Exception('The token has been blacklisted');
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::error('Driver::LoginService::refreshToken - Expired: '.$e->getMessage());
            throw new Exception('The token has expired');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('Driver::LoginService::refreshToken - JWT Error: '.$e->getMessage());
            throw new Exception('Could not refresh token');
        } catch (Exception $e) {
            Log::error('Driver::LoginService::refreshToken - General: '.$e->getMessage());
            throw new Exception($e->getMessage());
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
