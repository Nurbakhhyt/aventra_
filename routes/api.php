<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


use App\Http\Controllers\BookingController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TourController;


// API маршруты для работы с городами
Route::Resource('cities', CityController::class);

// API маршруты для работы с локациями
Route::Resource('locations', LocationController::class);

// API маршруты для работы с турами
Route::Resource('tours', TourController::class);

// Маршруты для бронирования (доступные только для авторизованных пользователей)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
});

// В случае необходимости добавьте другие API маршруты для дополнительных операций

// Пример логина и получения токена
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    return response()->json(['error' => 'Unauthorized'], 401);
});


Route::post('/register', function (Request $request) {
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    return response()->json([
        'token' => $user->createToken('API Token')->plainTextToken
    ]);
});


