<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('uid', Auth::user()->id)->get();
        return response()->json([
            'ResponseCode' => '200',
            'Result' => empty($notifications) ? 'false' : 'true',
            'ResponseMsg' => empty($notifications) ? 'Notification Not Found!!' : 'Notification List Get Successfully!!',
            'NotificationData' => $notifications
        ]);
    }
}
