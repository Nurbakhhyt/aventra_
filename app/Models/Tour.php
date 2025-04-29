<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'location_id',
        'price',
        'volume',
        'date',
        'image'
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

//    public function bookings()
//    {
//        return $this->hasMany(Booking::class);
//    }

    public function images()
    {
        return $this->hasMany(TourImage::class);
    }

    public function decreaseVolume(int $purchasedSeats)
    {
        if ($this->volume < $purchasedSeats) {
            throw new \Exception('Not enough seats available for this tour.');
        }

        $this->volume -= $purchasedSeats;
        $this->save();
    }
}
