<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class CarBrands extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'carBrands';

    protected $fillable = [
        'id', 'img', 'title', 'status'
    ];

}
