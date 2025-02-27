<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotCertificate extends Model
{
    use HasFactory;

    protected $table = 'pilotCertificates';

    protected $fillable = [
        'name', 'certificationNumber', 's3Key', 'address', 'sex', 'height', 'weight', 'hair', 'eyes', 'dayOfBirth', 'issueDate', 'expireDate', 'userId'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
