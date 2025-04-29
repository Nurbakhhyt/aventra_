<?php

// app/Http/Controllers/ReviewController.php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $tourId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
        ]);

        $tour = Tour::findOrFail($tourId);

        $review = new Review();
        $review->tour_id = $tour->id;
        $review->user_id = Auth::id();
        $review->rating = $request->rating;
        $review->content = $request->content;
        $review->save();

        return response()->json(['message' => 'Review added successfully'], 200);
    }

    public function index($tourId)
    {
        $tour = Tour::findOrFail($tourId);
        $reviews = $tour->reviews()->with('user')->get();

        return response()->json($reviews);
    }
}

