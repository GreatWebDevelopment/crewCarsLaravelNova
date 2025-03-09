<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FlutterwaveController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $email = $request->input('email');
        $amount = $request->input('amount');

        $paymentMethod = PaymentMethod::find(7);
        $attributes = explode(',', $paymentMethod->attributes);

        $payload = json_encode([
            'tx_ref' => time(),
            'amount' => $amount,
            'currency' => 'NGN',
            'payment_options' => 'card',
            'redirect_url' => env('APP_URL') . '/api/payment/flutterwave/success',
            'customer' => [
                'email' => $email,
                'name' => 'Zubdev',
            ],
            'meta' => [
                'price' => $amount,
            ],
            'customizations' => [
                'title' => 'Paying for a sample product',
                'description' => 'sample',
            ]
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $attributes[0],
        ];

        $response = Http::withHeaders($headers)->post(env('FLUTTERWAVE_API_URL') . '/v3/payments', $payload);
        if ($response->successful()) {
            $response = $response->json();
            if ($response['status'] === 'success') {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'The Payout Successful', 'link' => $response['link']]);
            }

            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'We can not process your payment'], 400);
        }
    }

    public function success(Request $request)
    {
        if ($request->has('status')) {
            $status = $request->input('status');

            if ($status === 'cancelled') {
                return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => urldecode($status)], 400);
            }
            else if ($status === 'completed') {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => urldecode($status), 'Transaction_id' => $request->input('transaction_id')]);
            }
            else if ($status === 'successful') {
                $transactionId = $request->input('transaction_id');

                $paymentMethod = PaymentMethod::find(7);
                $attributes = explode(',', $paymentMethod->attributes);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $attributes[0],
                ])->get(env('FLUTTERWAVE_API_URL') . '/v3/transactions/' . $transactionId . '/verify');

                if ($response->successful()) {
                    $response = $response->json();
                    if ($response['status']) {
                        $amountPaid = $response['charged_amount'];
                        $amountToPay = $response['meta']['price'];

                        if ($amountPaid >= $amountToPay) {
                            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => urldecode($status), 'Transaction_id' => $transactionId]);
                        } else {
                            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => urldecode($status)], 400);
                        }
                    } else {
                        return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => urldecode($status)], 400);
                    }
                }
            }
        }
    }
}
