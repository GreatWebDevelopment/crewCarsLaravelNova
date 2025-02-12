<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Car extends Model
{
    use HasFactory;
    use Searchable;
    protected $fillable = [
        'userId', 'brand', 'model', 'year', 'rentPrice',
        'engineHp', 'transmission', 'location', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'carId');
    }
}
