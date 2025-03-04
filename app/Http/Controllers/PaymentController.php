<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\PayoutSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function gateway()
    {
        $paymentMethods = PaymentMethod::where('status', 1)->get();
        return response()->json(["paymentdata"=>$paymentMethods, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Payment Gateway List Founded!'], 200);
    }

    public function requestWithdraw(Request $request)
    {
        if (!checkRequestParams($request, ['uid', 'amt', 'r_type'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $uid = $request->uid;
        $amt = $request->amt;
        $r_type = $request->r_type;

        $without_cod = Booking::where('bookingStatus', 'Completed')->where('postId', $uid)
            ->sum(DB::raw("(subtotal - couAmt) - ((subtotal - couAmt) * commission / 100)"));
        $without_cod = $without_cod ?: 0;

        $finalpayout = PayoutSettings::where('uid', $uid)->sum('amt');
        $finalpayout = $finalpayout ?: 0;

        $available_balance = number_format((float)($without_cod - $finalpayout), 2, '.', '');
        $minWithdrawLimit = app('set')->wlimit;
        $currency = app('set')->currency;

        if ($amt < $minWithdrawLimit) {
            return response()->json([
                "ResponseCode" => "401",
                "Result"       => "false",
                "ResponseMsg"  => "Minimum Withdraw Amount is $minWithdrawLimit$currency"
            ]);
        }

        if ($amt > $available_balance) {
            return response()->json([
                "ResponseCode" => "401",
                "Result"       => "false",
                "ResponseMsg"  => "You can't Withdraw Above Your Earning!"
            ]);
        }

        try {
            DB::beginTransaction();
            PayoutSettings::create([
                'uid'         => $uid,
                'amt'         => $amt,
                'status'      => 'pending',
                'rDate'      => Carbon::now(),
                'rType'      => $r_type,
                'accNumber'  => $request->input('acc_number'),
                'bankName'   => $request->input('bank_name'),
                'accName'    => $request->input('acc_name'),
                'ifscCode'   => $request->input('ifsc_code'),
                'upiId'      => $request->input('upi_id'),
                'paypalId'   => $request->input('paypal_id'),
            ]);
            DB::commit();

            return response()->json([
                "ResponseCode" => "200",
                "Result"       => "true",
                "ResponseMsg"  => "Payout Request Submitted Successfully!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "ResponseCode" => "500",
                "Result"       => "false",
                "ResponseMsg"  => "An error occurred while processing your request."
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function payoutSettingsList(Request $request)
    {
        if (!checkRequestParams($request, ['uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $ps = PayoutSettings::where('status', 1)->get();
        return response()->json([
            "Payoutlist"   => $ps,
            "ResponseCode" => "200",
            "Result"       => "true",
            "ResponseMsg"  => empty($ps) ? "Payout List Not Found!!" : "Payout List Get Successfully!!!",
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
