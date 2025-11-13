<?php
use App\Http\Controllers\API\V1\Auth\Driver\RegisterController as DriverRegisterController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['guest:api']], function ($router) {
// Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    Route::post('register', [DriverRegisterController::class, 'register']);
    Route::post('/verify-email', [DriverRegisterController::class, 'VerifyEmail']);
    Route::post('/resend-otp', [DriverRegisterController::class, 'ResendOtp']);
});
