<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaypalController extends Controller
{
    public function success(Request $request)
    {
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Payment Successful!', 'paymentId' => $request->input['paymentId']]);
    }

    public function cancel()
    {
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Payment Cancelled!']);
    }
}
