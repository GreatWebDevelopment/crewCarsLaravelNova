<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Car;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $fillable = [
        'userId', 'carId', 'totalDayOrHr', 'subtotal',
        'taxPer', 'taxAmt', 'oTotal', 'pMethodId',
        'transactionId', 'bookStatus', 'pickupDate',
        'pickupTime', 'returnDate', 'returnTime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'carId', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'bookingId');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'cityId');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'pMethodId');
    }
}
