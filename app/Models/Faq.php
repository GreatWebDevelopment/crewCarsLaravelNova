<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Faq extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'faqs';

    protected $fillable = ['id', 'question', 'answer', 'status'];
}
