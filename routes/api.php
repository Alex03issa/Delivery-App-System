<?php

use App\Http\Controllers\API\DriverTrackingController;
use App\Http\Controllers\API\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tracking API
|--------------------------------------------------------------------------
|
| These endpoints are called with fetch() from the Blade pages, so they use
| the normal session ("web" middleware: session + CSRF) instead of API tokens.
| All URLs are prefixed with /api by bootstrap/app.php.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    // Driver pushes their current GPS position (optionally tied to a delivery).
    Route::post('/driver/update-location', [DriverTrackingController::class, 'updateLocation'])
        ->middleware('role:driver');

    Route::post('/tracking/update-location', [TrackingController::class, 'update'])
        ->middleware('role:driver');

    // Latest known position of a driver (by the driver's user id).
    Route::get('/tracking/driver-location/{driverId}', [TrackingController::class, 'getLatest']);
});
