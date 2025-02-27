<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model
{
    use HasFactory;

    protected $table = 'driverLicenses';

    protected $fillable = [
        'name', 'licenseNumber', 's3Key', 'address', 'city', 'state', 'class', 'restrictions', 'country', 'placeOfBirth', 'zipCode', 'dayOfBirth', 'issueDate', 'expireDate', 'userId'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
