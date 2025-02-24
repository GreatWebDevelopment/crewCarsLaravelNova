<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Fav extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'favs';

    protected $fillable = ['id', 'uid', 'carId'];
}
