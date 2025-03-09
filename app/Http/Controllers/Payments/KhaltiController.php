<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KhaltiController extends Controller
{
    private $testApiUrl = 'https://a.khalti.com/api/v2';

    public function index(Request $request)
    {
        $amount = $request->input('amt');

        $paymentMethod = Payment::find(15);
        $attributes = explode(',', $paymentMethod->attributes);
        if ($attributes[1] == 0) {
            $apiUrl = $this->testApiUrl . '/epayment/initiate';
        } else {
            $apiUrl = env('KHALTI_API_URL') . '/epayment/initiate';
        }

        $payload = json_encode([
            'amount' => strval($amount * 100),
            'website_url' => 'https://example.com/',
            'return_url' => env('APP_URL') . '/api/payment/khalti/success',
            'purchase_order_id' => 'Order01',
            'purchase_order_name' => 'test',
            'customer_info' => [
                'name' => 'Test Bahadur',
                'email' => 'test@khalti.com',
                'phone' => '9800000001',
            ]
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'key ' . $attributes[0],
        ])->post($apiUrl, $payload);

        if ($response->successful()) {
            $response = $response->json();
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'The Payout Successful', 'link' => $response['payment_url']]);
        }
    }

    public function success(Request $request)
    {
        if ($request->has('txnId')) {
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Transaction Completed!', 'Transaction_id' => $request->input('txnId')]);
        }
    }
}
