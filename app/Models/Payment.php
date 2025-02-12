<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookId', 'amount', 'status', 'transactionId'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'bookId');
    }
}
