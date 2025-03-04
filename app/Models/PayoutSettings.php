<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutSettings extends Model
{
    use HasFactory;

    protected $table = 'payoutSettings';

    protected $attributes = ['proof' => ''];

    protected $fillable = [
        'uid',
        'amt',
        'status',
        'rDate',
        'rType',
        'accNumber',
        'bankName',
        'accName',
        'ifscCode',
        'upiId',
        'paypalId',
    ];
}
