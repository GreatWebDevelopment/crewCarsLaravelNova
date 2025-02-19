<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId', 'carId', 'totalDayOrHr', 'subtotal',
        'taxPer', 'taxAmt', 'oTotal', 'pMethodId',
        'transactionId', 'bookStatus', 'pickupDate',
        'pickupTime', 'returnDate', 'returnTime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'carId');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'bookingId');
    }
}
