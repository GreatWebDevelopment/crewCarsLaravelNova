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
        'userId', 'carId', 'totalDayOrHr', 'subtotal', 'type', 'brand', 'rateText', 'cancelReason',
        'taxPer', 'taxAmt', 'oTotal', 'pMethodId', 'postId', 'pickOtp', 'dropOtp', 'commission', 'totalRate',
        'transactionId', 'bookStatus', 'pickupDate', 'city', 'location', 'carPrice', 'wallAmt', 'isRate',
        'pickupTime', 'returnDate', 'returnTime', 'priceType', 'bookingType', 'exterPhoto', 'interPhoto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'carId', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'bookingId');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'pMethodId');
    }

    protected $casts = [
        'interPhoto' => 'array',
        'exterPhoto' => 'array',
    ];
}
