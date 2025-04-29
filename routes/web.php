<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TourController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



//// Маршруты подтверждения email
//
//Route::get('/', function () {
//    return view('welcome');
//});
//
//// Email Verification
//Route::middleware(['auth'])->group(function () {
//    Route::get('/email/verify', function () {
//        return view('auth.verify-email');
//    })->name('verification.notice');
//
//    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//        $request->fulfill();
//        return redirect('/admin');
//    })->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
//
//    Route::post('/email/verification-notification', function () {
//        request()->user()->sendEmailVerificationNotification();
//        return back()->with('message', 'Verification link sent!');
//    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
//});
//
//// Аутентификация (если используете Laravel UI)
//Auth::routes();
//
//
//Route::resource('cities', CityController::class);
//Route::resource('locations', LocationController::class);
//Route::resource('tours', TourController::class);
//
//Route::middleware('auth')->group(function () {
//    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
//    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
//});
//// Удалите, если не используете страницу home
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
