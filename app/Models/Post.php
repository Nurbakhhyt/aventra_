<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'title', // Жаңадан қосылған тақырып бағаны
        'content',
        'saved', // Жаңадан қосылған сақтау бағаны
    ];

    protected $casts = [
        'saved' => 'boolean', // 'saved' бағанын boolean типіне келтіру
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }
}
