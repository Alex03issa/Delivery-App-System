<?php


use App\Http\Controllers\API\DriverTrackingController;
use App\Http\Controllers\API\TrackingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/driver/update-location', [DriverTrackingController::class, 'update']);
});


Route::post('/tracking/update-location', [TrackingController::class, 'update']);

Route::get('/tracking/driver-location/{driverId}', [TrackingController::class, 'getLatest']);
