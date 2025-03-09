<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MidtransController extends Controller
{
    private $testApiUrl = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required',
           'email' => 'required|email',
           'phone' => 'required',
           'amt' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $paymentMethod = PaymentMethod::find(13);
        $attributes = explode(',', $paymentMethod->attributes);
        if ($attributes[2] == 0) {
            $apiUrl = $this->testApiUrl;
        } else {
            $apiUrl = env('MIDTRANS_API_URL');
        }

        $payload = json_encode([
           'transaction_details' => [
               'order_id' => uniqid() . uniqid(),
               'gross_amount' => $request->input('amt') * 1000,
           ],
            'credit_card' => ['secure' => true],
            'customer_details' => [
                'first_name' => $request->input('name'),
                'last_name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ],
            'callbacks' => [
                'finish' => env('AWS_URL') . '/api/payment/midtrans/success'
            ]
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($attributes[1]),
        ])->post($apiUrl, $payload);

        if ($response->successful()) {
            $response = $response->json();
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'The Payout Successful', 'link' => $response['redirect_url']]);
        }
    }

    public function success(Request $request)
    {
        if ($request->has('status_code')) {
            $statusCode = $request->input('status_code');

            if ($statusCode == 200) {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Transaction Completed!', 'Transaction_id' => $request->input('order_id')]);
            }
        }
    }
}
