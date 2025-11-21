<?php

use App\Http\Controllers\API\V1\Auth\Driver\LoginController as DriverLoginController;
use App\Http\Controllers\API\V1\Auth\Driver\RegisterController as DriverRegisterController;
use App\Http\Controllers\API\V1\User\AppLanguageController;
use App\Http\Controllers\API\V1\User\Driver\DriverController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'driver'], function ($router) {
    // Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    Route::group(['middleware' => ['guest:api']], function ($router) {
        Route::post('register', [DriverRegisterController::class, 'register']);
        Route::post('/verify-phone', [DriverRegisterController::class, 'verifyPhone']);
        Route::post('/resend-otp', [DriverRegisterController::class, 'resendOtp'])->middleware('throttle:2,1');
        // login
        Route::post('login', [DriverLoginController::class, 'login']);
        Route::post('verify-login-otp', [DriverLoginController::class, 'verifyLoginOtp']);
    });

    Route::group(['middleware' => ['auth:api', 'is_driver']], function ($router) {

        // app languages
        Route::get('app-languages', [AppLanguageController::class, 'index']);
        Route::post('set-language', [AppLanguageController::class, 'setLanguage']);

        // //driver profile
        Route::get('driver-profile', [DriverController::class, 'driverProfile']);
        Route::post('driver-profile/update', [DriverController::class, 'updateDriverProfile']);
        // //driver vehicles
        // Route::get('vehicles', [DriverVehicleController::class, 'index']);
        // Route::post('vehicles', [DriverVehicleController::class, 'store']);
        // Route::put('vehicles/{id}', [DriverVehicleController::class, 'update']);
        // Route::delete('vehicles/{id}', [DriverVehicleController::class, 'destroy']);
        // driver trips
        // Route::get('trips', [DriverTripController::class, 'index']);
        // Route::get('trips/{id}', [DriverTripController::class, 'show']);
        // Route::post('trips/{id}/accept', [DriverTripController::class, 'accept']);
        // Route::post('trips/{id}/reject', [DriverTripController::class, 'reject']);
        // Route::post('trips/{id}/cancel', [DriverTripController::class, 'cancel']);
        // Route::post('trips/{id}/complete', [DriverTripController::class, 'complete']);
        // Route::post('trips/{id}/start', [DriverTripController::class, 'start']);
        // Route::post('trips/{id}/end', [DriverTripController::class, 'end']);
        // Route::post('trips/{id}/rate', [DriverTripController::class, 'rate']);
        // Route::post('trips/{id}/review', [DriverTripController::class, 'review']);
        // Route::post('trips/{id}/feedback', [DriverTripController::class, 'feedback']);

        // //driver notifications
        // Route::get('notifications', [DriverNotificationController::class, 'index']);
        // Route::get('notifications/{id}', [DriverNotificationController::class, 'show']);
        // Route::post('notifications/{id}/read', [DriverNotificationController::class, 'read']);
        // Route::post('notifications/{id}/unread', [DriverNotificationController::class, 'unread']);
        // Route::post('notifications/{id}/delete', [DriverNotificationController::class, 'delete']);
        // Route::post('notifications/delete-all', [DriverNotificationController::class, 'deleteAll']);
        // Route::post('notifications/mark-all-as-read', [DriverNotificationController::class, 'markAllAsRead']);

        // //driver app languages
        // Route::get('app-languages', [DriverAppLanguageController::class, 'index']);
        // Route::post('set-language', [DriverAppLanguageController::class, 'setLanguage']);
        // //driver logout
        // Route::post('logout', [DriverLogoutController::class, 'logout']);

    });
});
