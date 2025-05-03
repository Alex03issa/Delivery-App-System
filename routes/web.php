<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\DeliveryRequestController;
use App\Http\Controllers\DriverTaskController;
use App\Http\Controllers\DriverAvailabilityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EarningController;

// GET: Show profile setup form
Route::get('/drivers/create', [DriverController::class, 'create'])->name('drivers.create');

// POST: Store driver profile data
Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');

Route::get('/delivery/create', [DeliveryRequestController::class, 'create'])->name('delivery_requests.create');
Route::post('/delivery/store', [DeliveryRequestController::class, 'store'])->name('delivery_requests.store');

// Driver Task Management Routes 
Route::get('/driver/tasks', [DriverTaskController::class, 'index'])->name('driver.tasks.index');
Route::get('/driver/tasks/{id}', [DriverTaskController::class, 'show'])->name('driver.tasks.show');
Route::post('/driver/tasks/{id}', [DriverTaskController::class, 'update'])->name('driver.tasks.update');
Route::get('/driver/deliveries/create', [DriverTaskController::class, 'create'])->name('driver.tasks.create');
Route::post('/driver/deliveries', [DriverTaskController::class, 'store'])->name('driver.tasks.store');

//driver availability
Route::get('/driver/availability', [DriverAvailabilityController::class, 'index'])->name('driver.availability.index');
Route::get('/driver/availability/create', [DriverAvailabilityController::class, 'create'])->name('driver.availability.create');
Route::post('/driver/availability', [DriverAvailabilityController::class, 'store'])->name('driver.availability.store');
Route::get('/driver/availability/{id}/edit', [DriverAvailabilityController::class, 'edit'])->name('driver.availability.edit');
Route::put('/driver/availability/{id}', [DriverAvailabilityController::class, 'update'])->name('driver.availability.update');
Route::delete('/driver/availability/{id}', [DriverAvailabilityController::class, 'destroy'])->name('driver.availability.destroy');

//notifications
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/store', [NotificationController::class, 'storeFcmToken'])->name('notifications.store');

//earnings


Route::get('/earnings', [EarningController::class, 'index'])->name('drivers.earnings.index');
Route::get('/earnings/create', [EarningController::class, 'create'])->name('drivers.earnings.create');
Route::post('/earnings', [EarningController::class, 'store'])->name('drivers.earnings.store');
Route::get('/earnings/{earning}', [EarningController::class, 'show'])->name('drivers.earnings.show');
Route::get('/earnings/{earning}/edit', [EarningController::class, 'edit'])->name('drivers.earnings.edit');
Route::put('/earnings/{earning}', [EarningController::class, 'update'])->name('drivers.earnings.update');
Route::delete('/earnings/{earning}', [EarningController::class, 'destroy'])->name('drivers.earnings.destroy');
