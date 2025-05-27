<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{

    public function index(){
         $bookings = Booking::with('tour')
                ->where('user_id', auth()->id())
                ->latest()
                ->get();

         return view('bookingTour.index', compact('bookings'));
    }

    public function tourCreate(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'seats' => 'nullable|integer|min:1'
        ]);

        $tour = Tour::with(['location', 'user', 'images'])->findOrFail($request->tour_id);
        $seats = $request->seats ?? 1;

        return view('bookingTour.create', compact('tour', 'seats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'seats' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Сіз кірмегенсіз.');
        }

        $tour = Tour::findOrFail($data['tour_id']);

        // Қолжетімді орын тексеру
        $activeSeats = Booking::where('tour_id', $tour->id)
            ->where('expires_at', '>', now())
            ->where('status', 'pending')
            ->sum('seats');

        $availableSeats = $tour->volume - $activeSeats;

        if ($availableSeats < $data['seats']) {
            return back()->with('error', 'Бұл турда жеткілікті орын жоқ.');
        }

        // Бронь жасау
        $booking = Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'seats' => $data['seats'],
            'status' => 'pending',
            'is_paid' => false,
            'expires_at' => now()->addMinutes(15),
        ]);

        session(['booking_id' => $booking->id]);

        return redirect()->route('bookingTour.index', [
            'tour_id' => $tour->id,
            'seats' => $data['seats']
        ])->with('success', 'Брондау сәтті жасалды. Енді төлем жасаңыз.');
    }


    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Бұл брондауды жоюға құқығыңыз жоқ.');
        }

        $booking->delete();

        return redirect()->route('bookingTour.index')->with('success', 'Брондау сәтті жойылды.');
    }

    public function userBookings()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $bookings = Booking::with('tour')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('bookings.index', compact('bookings'));
    }
}
