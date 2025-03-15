<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $amount = $request->input('amount');

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => app('set')->currency_format,
        ]);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Payment Intent Created Successfully!', 'client_secret' => $paymentIntent->client_secret]);
    }

    public function charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $amount = $request->input('amount');

        $charge = Charge::create([
            'amount' => $amount * 100,
            'currency' => app('set')->currency_format,
            'source' => $request->input('token'),
            'description' => 'Payment for wallet charge',
        ]);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Charge Successfully!', 'charge' => $charge]);
    }
}
