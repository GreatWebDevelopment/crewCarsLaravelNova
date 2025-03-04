<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timestamp = date("Y-m-d");
        $c = [];

        Coupon::where('status', 1)->get()->each(function ($coupon) use ($timestamp, $c) {
            if ($coupon['expireDate'] < $timestamp) {
                $coupon->status = 0;
                $coupon->save();
            } else {
                $c[] = $coupon;
            }
        });

        return response()->json([
            "couponlist" => $c,
            "ResponseCode" => "200",
            "Result" => !empty($c) ? "true" : "false",
            "ResponseMsg" => !empty($c) ? "Coupon List Founded!" : "Coupon Not Founded!"
        ]);
    }

    public function check(Request $request)
    {
        if (!checkRequestParams($request, ['cid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $coupon_exists = Coupon::where('id', $request->input('cid'))->exits();

        return response()->json([
            "ResponseCode" => "200",
            "Result" => $coupon_exists ? "true" : "false",
            "ResponseMsg" => $coupon_exists ? "Coupon Applied Successfully!!" : "Coupon Not Exist!!"
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
