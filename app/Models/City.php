<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class City extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'citys';

    protected $fillable = [
        'id', 'title', 'status'
    ];
}
