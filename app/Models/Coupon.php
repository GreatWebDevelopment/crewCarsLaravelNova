<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';
    protected $fillable = ['couponImg', 'title', 'couponCode', 'subtitle', 'expireDate', 'status'];
}
