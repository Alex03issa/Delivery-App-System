<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\FacebookAuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\signupController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\OtpController;

use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\DeliveryController;

use App\Http\Controllers\DriverPanelController;

use App\Http\Controllers\ClientTrackingController;

use App\Http\Controllers\ChatsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CryptoController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('home', [
        'title' => 'Cabs Online | Book A Taxi Ride With Us Today!']);
})->name('homepage');


Route::get('/about', function () {
    return view('about', [
        'title' => 'About | Cabs Online']);
});


Route::get('/admin', function () {
    return view('admin.index', [
        'title' => 'Dashboard Admin | Cabs Online',
    ]);
})->middleware('auth')->name('admin.dashboard');



// MY OWN ROUTES

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');


Route::get('/auth/facebook/redirect', [FacebookAuthController::class, 'redirectToFacebook'])->name('facebook.redirect');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('facebook.callback');


Route::get('/verify-email/{token}', [VerificationController::class, 'verify'])->name('verify.email');


Route::get('/login', [LoginController::class, 'showSignIn'])->name('login');
Route::post('/login', [LoginController::class, 'signIn'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [signupController::class, 'showSignUp'])->name('register');
Route::post('/register', [signupController::class, 'signUp'])->name('register.submit');



Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');



Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');



Route::get('/client/dashboard', [ClientDashboardController::class, 'show'])->name('client.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/client/deliveries', [ClientDashboardController::class, 'index'])->name('client.deliveries');
});

Route::get('/otp/verify', [OtpController::class, 'showForm'])->name('otp.verify.view');
Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify.submit');


Route::get('/driver/details/{user_id}', [DriverController::class, 'show'])->name('driver.register.details');
Route::post('/driver/details/{user_id}', [DriverController::class, 'store'])->name('driver.register.save');

Route::get('/home', [VerificationController::class, 'showHomepageWithVerification'])->middleware(['auth'])->name('home.verified');



Route::get('/driver/dashboard', function () {
    return view('dashboards.driverdahsboards', ['title' => 'Driver Dashboard']);
})->name('driver.dashboard');



Route::middleware(['auth'])->group(function () {
    Route::get('/delivery/create', [DeliveryController::class, 'create'])->name('delivery.create');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/track/{id}', [DeliveryController::class, 'track'])->name('delivery.track');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/driver/deliveries', [DriverPanelController::class, 'index'])->name('driver.deliveries');
    Route::post('/driver/deliveries/{id}/start', [DriverPanelController::class, 'startDelivery'])
    ->name('driver.delivery.start');
    Route::get('/driver/deliveries/{id}/start', [DriverPanelController::class, 'start'])
    ->name('driver.deliveries.start');
    Route::get('/driver/available-deliveries', [DriverPanelController::class, 'available'])->name('driver.available');
    Route::post('/driver/accept/{id}', [DriverPanelController::class, 'accept'])->name('driver.accept');
    

});


Route::middleware(['auth'])->get('/client/deliveries/{id}/track', [ClientTrackingController::class, 'track'])->name('client.track');

 Route::get('chat', [ChatsController::class, 'index'])->name('chat');

 Route::post('send-message', [ChatsController::class, 'sendMessage'])->name('send-message');

 Route::get('communication-history', [ChatsController::class, 'getChatHistory'])->name('communication-history');

 Route::post('upload-communication-photo', [ChatsController::class, 'uploadImage'])->name('upload-communication-photo');

 Route::get('get-new-messages/{user_id?}', [ChatsController::class, 'getNewMessages'])->name('get-new-messages');



 Route::middleware(['auth'])->group(function () {
    Route::get('/payment/form/{delivery}', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
    Route::post('/payment/store', [PaymentController::class, 'store'])->name('payment.store');
});


Route::get('/payment/stripe/{payment}', action: [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('/payment/success/{payment}', [StripeController::class, 'success'])->name('payment.success');
Route::get('/payment/crypto/{payment}', [CryptoController::class, 'checkout'])->name('payment.crypto');
Route::post('/webhook/coinbase', [CryptoController::class, 'webhook']);
