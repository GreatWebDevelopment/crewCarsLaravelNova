<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class CarTypes extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'carTypes';

    protected $fillable = [
        'id', 'img', 'title', 'status'
    ];

}
