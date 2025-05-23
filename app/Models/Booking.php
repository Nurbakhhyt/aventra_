<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['user_id', 'tour_id', 'seats', 'is_paid', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentsTour(){
        return $this->hasMany(PaymentTour::class);
    }
}

