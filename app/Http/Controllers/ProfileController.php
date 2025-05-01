<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    public function show($id)
    {
        try {
            $user = User::with([
                'posts',
                'comments',
                'reviews.tour',
                'tours',
                'bookings.tour',
                'favoriteTours'
            ])->findOrFail($id);

            return response()->json($user);
        } catch (\Exception $e) {
            \Log::error('Ошибка профиля: '.$e->getMessage());
            return response()->json(['message' => 'Ошибка сервера'], 500);
        }
    }


}
