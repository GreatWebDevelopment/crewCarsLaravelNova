<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;


class Car extends Model
{
    use HasFactory;
    use Searchable;
    protected $table = 'cars';
    protected $with = ['bookings'];
    protected $fillable = [
        'userId', 'title', 'number', 'img', 'status', 'rating', 'seats', 'ac', 'driverName', 'brand', 'rentPrice',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }


    public function bookings()
    {
        Log::info('Car::bookings() method was called.');
        return $this->hasMany(Booking::class, 'carId', 'id');
    }

    public function setCarNumberAttribute($value)
    {
        $this->attributes['number'] = $value;
    }

    public function setCarStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }

    public function setCarTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
    }

    public function setCarRatingAttribute($value)
    {
        $this->attributes['rating'] = $value;
    }

    public function setTotalSeatAttribute($value)
    {
        $this->attributes['seats'] = $value;
    }

    public function setCarAcAttribute($value)
    {
        $this->attributes['ac'] = $value;
    }

//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
//
//    public function setAttribute($value)
//    {
//        $this->attributes[''] = $value;
//    }
}
