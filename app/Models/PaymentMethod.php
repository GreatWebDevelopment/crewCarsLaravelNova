<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'paymentMethods'; // Ensure it matches your DB table name

    protected $fillable = [
        'title',
        'image',
        'attributes',
        'status',
        'subtitle',
        'isVisible'
    ];
}
