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

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(TourImage::class);
    }


    public function decreaseVolume(int $seats): void
    {
        if ($this->volume < $seats) {
            throw new \Exception('Недостаточно мест для уменьшения.');
        }

        $this->volume -= $seats;
        $this->save();
    }

    public function increaseVolume(int $seats): void
    {
        $this->volume += $seats;
        $this->save();
    }

}
