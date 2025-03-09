<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaytmController extends Controller
{
    private $PAYTM_MERCHANT_MID;
    private $PAYTM_MERCHANT_KEY;
    private $PAYTM_ENVIRONMENT;

    public function __construct()
    {
        $paymentMethod = PaymentMethod::find(8);
        $attributes = explode(',', $paymentMethod->attributes);
        $this->PAYTM_MERCHANT_MID = $attributes[0];
        $this->PAYTM_MERCHANT_KEY = $attributes[1];
        $this->PAYTM_ENVIRONMENT = $attributes[2];
    }

    public function index(Request $request)
    {
        $payload = [
            'ORDER_ID' => time(),
            'CUST_ID' => $request->input('uid'),
            'MOBILE_NO' => '7777777777',
            'EMAIL' => 'test@gmail.com',
            'TXN_AMOUNT' => $request->input('amt'),
            'MID' => $this->PAYTM_MERCHANT_MID,
            'CHANNEL_ID' => 'WEB',
            'WEBSITE' => '',
            'INDUSTRY_TYPE_ID' => '',
            'CALLBACK_URL' => env('APP_URL') . '/api/payment/paytm/success',
        ];

        $checksum = getChecksumFromArray($payload, $this->PAYTM_MERCHANT_KEY);
        $payload['CHECKSUMHASH'] = $checksum;
        $transactionUrl = env('PAYTM_API_URL') . '/theia/processTransaction';

        $response = Http::asForm()->post($transactionUrl, $payload);
        if ($response->successful()) {
            return redirect($response->body());
        }
    }

    public function success(Request $request)
    {
        $payload = $request->all();
        $checksum = $request->has('CHECKSUMHASH') ? $request->input('CHECKSUMHASH') : '';
        $isValidChecksum = verifychecksum_e($payload, $this->PAYTM_MERCHANT_KEY, $checksum);

        if ($isValidChecksum == 'TRUE') {
            if ($request->has('STATUS')) {
                $status = $request->input('STATUS');
                if ($status == 'TXN_SUCCESS') {
                    return redirect('/payment/paytm/success?status=successful&transaction_id=' . $request->input('TXN_ID'));
                } else {
                    return redirect('/payment/paytm/success?status=failed&transaction_id=' . $request->input('TXN_ID'));
                }
            }
        } else {
            $status = $request->input('STATUS');
            if ($status == 'successful') {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => urldecode($status), 'Transaction_id' => $request->input('transaction_id')]);
            } else if ($status == 'failed') {
                return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => urldecode($status)], 400);
            } else {
                return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Checksum mismatched!'], 400);
            }
        }
    }
}
