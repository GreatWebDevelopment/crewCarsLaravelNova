<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletReport extends Model
{
    use HasFactory;

    protected $table = 'walletReports';

    protected $fillable = ['uid', 'message', 'status', 'amt', 'tdate'];
}
