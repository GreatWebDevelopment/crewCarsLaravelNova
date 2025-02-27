<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletReport extends Model
{
    protected $table = 'walletReports';
    protected $fillable = ['uid', 'message', 'status', 'amt', 'tdate'];
}
