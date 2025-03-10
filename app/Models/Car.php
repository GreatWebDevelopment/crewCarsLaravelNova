<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Car extends Model
{
    use HasFactory;
    use Searchable;
    protected $table = 'cars';
    protected $fillable = [
        'userId', 'title', 'number', 'img', 'status', 'rating', 'seats', 'ac', 'driverName', 'brand', 'rentPrice',
        'location', 'type', 'brand', 'postId', 'description', 'driverName', 'driverMobile', 'transmission', 'facility', 'available', 'rentPriceDriver',
        'engineHp', 'priceType', 'fuelType', 'pickAddress', 'pickLat', 'pickLng', 'totalMiles', 'minHrs', 'city'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'carId', 'id');
    }

    public function getCarRateAttribute()
    {
        $rating = $this->bookings()
            ->where('bookingStatus', 'Completed')
            ->where('isRate', 1)
            ->avg('totalRate');

        return $rating ? number_format($rating, 2) : $this->rating;
    }

    public function calculateDistance($lat, $lng)
    {
        $pick_lat = $this->pickLat;
        $pick_lng = $this->pickLng;

        // Haversine formula to calculate distance between two points (in kilometers)
        $earth_radius = 6371; // Radius of Earth in kilometers

        $lat_diff = deg2rad($lat - $pick_lat);
        $lng_diff = deg2rad($lng - $pick_lng);

        $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
            cos(deg2rad($pick_lat)) * cos(deg2rad($lat)) *
            sin($lng_diff / 2) * sin($lng_diff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earth_radius * $c; // Distance in kilometers

        // Optionally, you can convert the distance to miles if needed
        $distance_in_miles = $distance * 0.621371;

        return $distance_in_miles;  // Return distance in miles
    }

    protected $casts = [
        'img' => 'array',
    ];

}
