<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request) {
        if (!checkRequestParams($request, ['uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $notifications = Notification::where('uid', $request->input('uid'))->get();
        return response()->json([
            'ResponseCode' => '200',
            'Result' => empty($notifications) ? 'false' : 'true',
            'ResponseMsg' => empty($notifications) ? 'Notification Not Found!!' : 'Notification List Get Successfully!!',
            'NotificationData' => $notifications
]);
}
}
