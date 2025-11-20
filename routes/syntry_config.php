<?php

use App\Helpers\Helper;
use Illuminate\Support\Facades\Route;

// =========== Sentry Config Routes ============

// VEHICLE
Route::get('/sentry/vehicle-status', function () {
    return Helper::jsonResponse(true, 'Vehicle statuses fetched successfully', 200, config('sentry.vehicle_status'));
});
Route::get('/sentry/vehicle-type', function () {
    return Helper::jsonResponse(true, 'Vehicle types fetched successfully', 200, config('sentry.vehicle_type'));
});
Route::get('/sentry/license-category', function () {
    return Helper::jsonResponse(true, 'License categories fetched successfully', 200, config('sentry.license_plate_category'));
});
Route::get('/sentry/commercial-license-type', function () {
    return Helper::jsonResponse(true, 'Commercial license types fetched successfully', 200, config('sentry.commercial_license_type'));
});
Route::get('/sentry/vehicle-states', function () {
    return Helper::jsonResponse(true, 'Vehicle States fetched successfully', 200, config('sentry.vehicle_states'));
});

// DRIVER
Route::get('/sentry/driver-status', function () {
    return Helper::jsonResponse(true, 'Driver statuses fetched successfully', 200, config('sentry.driver_status'));
});
Route::get('/sentry/driver-states', function () {
    return Helper::jsonResponse(true, 'Driver States fetched successfully', 200, config('sentry.driver_states'));
});
Route::get('/sentry/driver-gender', function () {
    return Helper::jsonResponse(true, 'Driver gender fetched successfully', 200, config('sentry.gender'));
});
Route::get('/sentry/driver-license-class', function () {
    return Helper::jsonResponse(true, 'Driver license classes fetched successfully', 200, config('sentry.driver_license_class'));
});
Route::get('/sentry/dmv-endorsements', function () {
    return Helper::jsonResponse(true, 'DMV endorsements fetched successfully', 200, config('sentry.dmv_endorsements'));
});
Route::get('/sentry/dmv-restrictions', function () {
    return Helper::jsonResponse(true, 'DMV restrictions fetched successfully', 200, config('sentry.dmv_restrictions'));
});
Route::get('/sentry/extra-commercial-license-type', function () {
    return Helper::jsonResponse(true, 'Extra commercial license types fetched successfully', 200, config('sentry.extra_commercial_license_types'));
});
Route::get('/sentry/credential-status', function () {
    return Helper::jsonResponse(true, 'Credential statuses fetched successfully', 200, config('sentry.credential_status'));
});

