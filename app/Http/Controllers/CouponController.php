<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $c = Coupon::where('status', 1)->where('expireDate',  '>=', Carbon::now())->get();
        Coupon::where('status', 1)->where('expireDate',  '<', Carbon::now())->update(['status' => 0]);

        return response()->json([
            "couponlist" => $c,
            "ResponseCode" => "200",
            "Result" => !$c->isEmpty() ? "true" : "false",
            "ResponseMsg" => !$c->isEmpty() ? "Coupon List Founded!" : "Coupon Not Founded!"
        ]);
    }

    public function check(Request $request)
    {
        if (!checkRequestParams($request, ['cid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $coupon_exists = Coupon::where('id', $request->input('cid'))->exists();

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
