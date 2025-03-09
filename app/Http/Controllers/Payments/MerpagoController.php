<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use stdClass;

class MerpagoController extends Controller
{
    public function index(Request $request)
    {
        $paymentMethod = PaymentMethod::find(11);
        $attributes = explode(',', $paymentMethod->attributes);

        $payload = json_encode([
            'differential_pricing' => null,
            'expires' => false,
            'items' => [
                [
                    'title' => 'Dummy Title',
                    'description' => 'Dummy description',
                    'picture_url' => 'http://www.myapp.com/myimage.jpg',
                    'category_id' => 'car_electronics',
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                    'unit_price' => intval($request->input('amt')),
                ]
            ],
            'marketplace_fee' => null,
            'metadata' => null,
            'payer' => [
                'phone' => ['number' => null],
                'identification' => new stdClass(),
                'address' => ['street_number' => null]
            ],
            'payment_methods' => [
                'excluded_payment_methods' => [new stdClass()],
                'excluded_payment_types' => [new stdClass()],
                'installments' => null,
                'default_installments' => null
            ],
            'back_urls' => [
                'success' => env('APP_URL') . '/api/payment/merpago/success',
                'failure' => env('APP_URL') . '/api/payment/merpago/fail',
                'pending' => env('APP_URL') . '/api/payment/merpago/pending'
            ],
            'redirect_urls' => [
                'success' => env('APP_URL') . '/api/payment/merpago/success',
                'failure' => env('APP_URL') . '/api/payment/merpago/fail',
                'pending' => env('APP_URL') . '/api/payment/merpago/pending'
            ],
            'shipments' => [
                'local_pickup' => false,
                'default_shipping_method' => null,
                'free_methods' => [['id' => null]],
                'cost' => null,
                'free_shipping' => false,
                'receiver_address' => ['street_number' => null]
            ],
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $attributes[0]
        ])->post(env('MERPAGO_API_URL'), $payload);

        if ($response->successful()) {
            $response = $response->json();
            if ($attributes[1] == 0) {
                $url = $response['sandbox_init_point'];
            } else {
                $url = $response['init_point'];
            }
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'The Payout Successful', 'link' => $url]);
        }
    }

    public function success(Request $request)
    {
        return response()->json($request->all());
    }

    public function fail(Request $request)
    {
        return response()->json($request->all());
    }

    public function pending(Request $request)
    {
        return response()->json($request->all());
    }
}
