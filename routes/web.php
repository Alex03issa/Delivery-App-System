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

Route::resource('/booking', PassengerController::class);
Route::match(['get', 'post'], '/continue-booking', [PassengerController::class, 'continueBooking']);

Route::get('/cancel-booking', [PassengerController::class, 'cancelBooking']);

Route::get('/register', [RegisterController::class, 'index'])
    ->middleware('guest');
Route::post('/register', [RegisterController::class, 'store']);


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



Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');


Route::get('/otp/verify', [OtpController::class, 'showForm'])->name('otp.verify.view');
Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify.submit');
