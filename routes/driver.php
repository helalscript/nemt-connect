<?php

use App\Http\Controllers\API\V1\Auth\Driver\LoginController as DriverLoginController;
use App\Http\Controllers\API\V1\Auth\Driver\RegisterController as DriverRegisterController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:api'], 'prefix' => 'driver'], function ($router) {
    // Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    Route::post('register', [DriverRegisterController::class, 'register']);
    Route::post('/verify-phone', [DriverRegisterController::class, 'verifyPhone']);
    Route::post('/resend-otp', [DriverRegisterController::class, 'resendOtp']);
    // login
    Route::post('login', [DriverLoginController::class, 'login']);
    Route::post('verify-login-otp', [DriverLoginController::class, 'verifyLoginOtp']);
});
