<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name','img','description','city_code'];

    public function locations(){
        return $this -> hasMany(Location::class,'city_id');
    }

    public function hotels(){
        return $this->hasMany(Hotel::class, 'city');
    }

}
