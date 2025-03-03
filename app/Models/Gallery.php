<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'gallerys';

    protected $fillable = ['uid', 'carId', 'img'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'carId');
    }

    protected $casts = [
        'img' => 'array',
    ];

}
