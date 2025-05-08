<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteTourController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
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



//Login
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    return response()->json(['error' => 'Unauthorized'], 401);
});


//Register
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
    return $request->user()->load('tours', 'bookings', 'favoriteTours'); // Қажетті қатынастарды жүктеңіз
});
Route::middleware('auth:sanctum')->get('/user/reviews', [ReviewController::class, 'userReviews'])->name('user.reviews');
Route::middleware('auth:sanctum')->post('/update-profile', [ProfileController::class, 'update'])->name('profile.update');
//Routes which don't use auth
Route::apiResource('cities', CityController::class);
Route::apiResource('locations', LocationController::class);
Route::apiResource('tours', TourController::class);
Route::get('/posts',[PostController::class,'index'])->name('posts.index');
Route::get('/posts/{id}',[PostController::class,'show'])->name('posts.show');

//Routes use auth
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/user', [BookingController::class, 'userBookings'])->name('bookings.user');

    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('favorites', FavoriteTourController::class);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::post('/posts/{post}/like', [LikeController::class, 'store'])->name('posts.like');
    Route::delete('/posts/{post}/like', [LikeController::class, 'destroy'])->name('posts.unlike');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::put('/post/{post}', [PostController::class, 'update'])->name('posts.update');

});


//Route::apiResource('posts', PostController::class);


