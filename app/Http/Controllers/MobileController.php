<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileController extends Controller
{
    public function checkMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'ccode' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $mobile = strip_tags($request->post('mobile'));
        $countryCode = strip_tags($request->post('ccode'));
        $users = User::where(function ($query) use ($mobile, $countryCode) {
            $query->where('mobile', $mobile)
                ->where('countryCode', $countryCode)
                ->where('role', 'user');
        })
            ->orWhere(function ($query) use ($mobile) {
                $query->where('mobile', $mobile)
                    ->where('role', 'admin');
            })->get();

        if (count($users) > 0) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Already Exist Mobile Number!'], 400);
        }

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'New Number!']);
    }
}
