<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Car extends Model
{
    use HasFactory;
    use Searchable;
    protected $fillable = [
        'userId', 'title', 'number', 'img', 'status', 'rating', 'seats', 'ac', 'driverName', 'brand', 'rentPrice', 'location', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'carId');
    }
}
