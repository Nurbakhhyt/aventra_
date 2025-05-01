<?php

use App\Http\Controllers\FavoriteTourController;
use App\Http\Controllers\ReviewController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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

Route::Resource('reviews', ReviewController::class);
Route::Resource('favorites', FavoriteTourController::class);

// Маршруты для бронирования (доступные только для авторизованных пользователей)
Route::middleware('auth:sanctum')->group(function () {
    // ... басқа авторизацияланған маршруттар ...
    Route::get('/reviews/user', [ReviewController::class, 'userReviews'])->name('reviews.user');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/user', [BookingController::class, 'userBookings'])->name('bookings.user');
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

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

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
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
