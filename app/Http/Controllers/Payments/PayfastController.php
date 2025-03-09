<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayfastController extends Controller
{
    private $testApiUrl = 'sandbox.payfast.co.za/eng/process';

    public function index(Request $request)
    {
        $amount = $request->input('amt');
        $paymentId = uniqid();
        $passphrase = 'jt7NOE43FZPn';

        $paymentMethod = PaymentMethod::find(12);
        $attributes = explode(',', $paymentMethod->attributes);

        $payload = [
            'merchant_id' => $attributes[1],
            'merchant_key' => $attributes[0],
            'return_url' => env('APP_URL') . '/api/payment/payfast/success?payment_id=' . $paymentId . '&status=success',
            'cancel_url' => env('APP_URL') . '/api/payment/payfast/cancel?payment_id=' . $paymentId . '&status=failed',
            'notify_url' => env('APP_URL') . '/api/payment/payfast/success?payment_id=' . $paymentId . '&status=success',
            'name_first' => 'First Name',
            'name_last' => 'Last Name',
            'email' => 'test@test.com',
            'm_payment_id' => $paymentId,
            'amount' => number_format( sprintf( '%.2f', $amount ), 2, '.', '' ),
            'item_name' => 'Order#123',
            'currency' => 2,
        ];

        $sign = $this->generateSign($payload, $passphrase);
        $payload['signature'] = $sign;

        if ($attributes[2] == 0) {
            $apiUrl = $this->testApiUrl;
        } else {
            $apiUrl = env('PAYFAST_API_URL');
        }

        $response = Http::asForm()->post($apiUrl, $payload);
        if ($response->successful()) {
            return redirect($response->body());
        }
    }

    public function success(Request $request)
    {
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Payment Successful!', 'Transaction_id' => $request->input('payment_id')]);
    }

    public function cancel(Request $request)
    {
        return response()->json(['ResponseCode' => '200', 'Result' => 'false', 'ResponseMsg' => 'Payment Failed!', 'Transaction_id' => $request->input('payment_id')]);
    }

    private function generateSign($payload, $passphrase)
    {
        $plainText = '';

        foreach ($payload as $key => $val) {
            if ($val !== '') {
                $plainText .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }

        $plainText = substr($plainText, 0, -1);
        if ($passphrase !== null) {
            $plainText .= '&passphrase=' . urlencode(trim($passphrase));
        }

        return md5($plainText);
    }
}
