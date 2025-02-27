<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'gallerys';

    protected $fillable = ['uid', 'carId', 'img'];
}
