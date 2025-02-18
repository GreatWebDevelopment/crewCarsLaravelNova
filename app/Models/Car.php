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

}
