<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $table = 'insurances';

    protected $fillable = [
        'name', 's3Key', 'policyNumber', 'naicNumber', 'insurer', 'year', 'make', 'model', 'vin', 'issueDate', 'expireDate', 'userId'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
