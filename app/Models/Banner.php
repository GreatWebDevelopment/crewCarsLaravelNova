<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function bookings()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    protected $fillable = ['title', 'image_url'];
}