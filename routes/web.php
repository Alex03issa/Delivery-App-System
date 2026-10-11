<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\FacebookAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ClientTrackingController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverPanelController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\signupController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home', [
        'title' => config('app.name') . ' | Fast package delivery with live tracking']);
})->name('homepage');

Route::get('/about', function () {
    return view('about', [
        'title' => 'About | ' . config('app.name')]);
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

Route::get('/auth/facebook/redirect', [FacebookAuthController::class, 'redirectToFacebook'])->name('facebook.redirect');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('facebook.callback');

Route::get('/verify-email/{token}', [VerificationController::class, 'verify'])->name('verify.email');

Route::get('/login', [LoginController::class, 'showSignIn'])->name('login');
Route::post('/login', [LoginController::class, 'signIn'])->middleware('throttle:10,1')->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [signupController::class, 'showSignUp'])->name('register');
Route::post('/register', [signupController::class, 'signUp'])->middleware('throttle:10,1')->name('register.submit');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');

Route::get('/otp/verify', [OtpController::class, 'showForm'])->name('otp.verify.view');
Route::post('/otp/verify', [OtpController::class, 'verify'])->middleware('throttle:5,1')->name('otp.verify.submit');

Route::get('/home', [VerificationController::class, 'showHomepageWithVerification'])->middleware(['auth'])->name('home.verified');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

/*
|--------------------------------------------------------------------------
| Client area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client/dashboard', [ClientDashboardController::class, 'show'])->name('client.dashboard');
    Route::get('/client/deliveries', [ClientDashboardController::class, 'index'])->name('client.deliveries');
    Route::get('/client/deliveries/{id}/track', [ClientTrackingController::class, 'track'])->name('client.track');

    Route::get('/delivery/create', [DeliveryController::class, 'create'])->name('delivery.create');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');

    // Payments (ownership is checked in the controllers)
    Route::get('/payment/form/{delivery}', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
    Route::post('/payment/store', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/payment/stripe/{payment}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/payment/success/{payment}', [StripeController::class, 'success'])->name('payment.success');
    Route::get('/payment/return/{payment}', [PaymentController::class, 'returned'])->name('payment.return');
    Route::get('/payment/crypto/{payment}', [CryptoController::class, 'checkout'])->name('payment.crypto');
    Route::get('/payment/nowpayments/{payment}', [CryptoController::class, 'nowPaymentsCheckout'])->name('nowpayments.checkout');
});

// Payment provider webhooks (signed, CSRF-exempt in bootstrap/app.php)
Route::post('/webhook/coinbase', [CryptoController::class, 'webhook'])->name('webhook.coinbase');
Route::post('/webhook/nowpayments', [CryptoController::class, 'nowPaymentsWebhook'])->name('webhook.nowpayments');

/*
|--------------------------------------------------------------------------
| Shared: delivery tracking page (client owner, assigned driver, admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/delivery/track/{id}', [DeliveryController::class, 'track'])->name('delivery.track');
});

/*
|--------------------------------------------------------------------------
| Driver area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:driver'])->group(function () {
    // Driver profile (vehicle, licence) completed right after email verification
    Route::get('/driver/details', [DriverController::class, 'show'])->name('driver.register.details');
    Route::post('/driver/details', [DriverController::class, 'store'])->name('driver.register.save');

    Route::get('/driver/dashboard', [DriverController::class, 'dashboard'])->name('driver.dashboard');
    Route::get('/driver/deliveries', [DriverPanelController::class, 'index'])->name('driver.deliveries');
    Route::post('/driver/deliveries/{id}/start', [DriverPanelController::class, 'startDelivery'])->name('driver.delivery.start');
    Route::get('/driver/deliveries/{id}/start', [DriverPanelController::class, 'start'])->name('driver.deliveries.start');
    Route::get('/driver/available-deliveries', [DriverPanelController::class, 'available'])->name('driver.available');
    Route::post('/driver/accept/{id}', [DriverPanelController::class, 'accept'])->name('driver.accept');
    Route::post('/driver/delivery/{delivery}/complete', [DriverController::class, 'completeDelivery'])->name('driver.delivery.complete');
});

/*
|--------------------------------------------------------------------------
| Chat between a client and the driver of one of their deliveries
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:client,driver'])->group(function () {
    Route::get('chat', [ChatsController::class, 'index'])->name('chat');
    Route::post('send-message', [ChatsController::class, 'sendMessage'])->name('send-message');
    Route::get('communication-history', [ChatsController::class, 'getChatHistory'])->name('communication-history');
    Route::get('get-new-messages/{user_id}', [ChatsController::class, 'getNewMessages'])->name('get-new-messages');
});
