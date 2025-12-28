<?php

use App\Http\Controllers\API\V1\Admin\DriverController;
use App\Http\Controllers\API\V1\Admin\JoinRequestController;
use App\Http\Controllers\API\V1\Admin\System\DynamicPageController;
use App\Http\Controllers\API\V1\Admin\SystemSettingController;
use Illuminate\Support\Facades\Route;

// this is for admin
Route::group(['middleware' => ['auth:api', 'is_admin'], 'prefix' => 'admin','as' => 'admin.'], function ($router) {
    Route::apiResource('dynamic-pages', DynamicPageController::class);
    Route::group(['prefix' => 'system'], function ($router) {
        Route::get('settings', [SystemSettingController::class, 'index']);
        Route::post('settings', [SystemSettingController::class, 'update']);
        Route::get('mail-settings', [SystemSettingController::class, 'getMailSetting']);
        Route::post('mail-settings', [SystemSettingController::class, 'updateMailSetting']);
        Route::get('clear-cache', [SystemSettingController::class, 'clearCache']);
    });

    Route::apiResource('join-requests', JoinRequestController::class);

    // Driver routes
    Route::apiResource('drivers', DriverController::class);
});
