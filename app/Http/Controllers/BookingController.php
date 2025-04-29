<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'seats' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $tour = Tour::findOrFail($data['tour_id']);

        // Проверка количества мест (учитывая уже занятые активными бронированиями)
        $activeBookings = Booking::where('tour_id', $tour->id)
            ->where('expires_at', '>', now())
            ->sum('seats');

        $availableSeats = $tour->volume - $activeBookings;

        if ($availableSeats < $data['seats']) {
            return redirect()->back()->with('error', 'Недостаточно свободных мест для бронирования.');
        }

        // Создание брони с истечением через 15 минут
        Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'seats' => $data['seats'],
            'is_paid' => false,
            'expires_at' => now()->addMinutes(15),
        ]);

        return redirect()->route('tours.index')->with('success', 'Тур успешно забронирован! У вас есть 15 минут, чтобы завершить оплату.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Недостаточно прав для отмены этой брони.');
        }

        $booking->delete();

        return redirect()->route('tours.index')->with('success', 'Бронь успешно отменена.');
    }
}
