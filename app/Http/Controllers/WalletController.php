<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\WalletReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{

    public function walletUp(Request $request) {
        if (!checkRequestParams($request, ['wallet'])) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }
        $wallet = strip_tags($request->input('wallet'));
        $uid = Auth::user()->id;

        $userExists = User::where('id', $uid)->exists();
        if ($userExists) {
            $vp = User::find($uid);

            $vp->walletBalance += $wallet;
            $vp->save();

            WalletReport::create([
                'uid' => $uid,
                'message' => 'Wallet Balance Added!!',
                'status' => 'Credit',
                'amt' => $wallet,
                'tdate' => Carbon::now()->format('Y-m-d'),
            ]);

            $updatedWallet = User::find($uid);
            return response()->json(['wallet' => $updatedWallet->walletBalance, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Wallet Update Successfully!!!'], 200);
        } else {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'User Deactivate By Admin!!!'], 400);
        }
    }

    public function walletReport() {
        $uid = Auth::user()->id;
        $userExists = User::where('id', $uid)->exists();

        if ($userExists) {
            $wallet = User::find($uid)->walletBalance;

            $walletReports = WalletReport::where('uid', $uid)
                ->orderBy('id', 'desc')
                ->get();

            $myarray = [];
            $l = 0;
            $k = 0;

            foreach ($walletReports as $row) {
                if ($row->status == 'Credit') {
                    $l += $row->amt;
                } else {
                    $k += $row->amt;
                }
                $p = [
                    'message' => $row->message,
                    'status' => $row->status,
                    'amt' => $row->amt,
                ];
                $myarray[] = $p;
            }

            return response()->json(['Walletitem' => $myarray, 'wallet' => $wallet, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Wallet Report Get Successfully!'], 200);
        } else {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Request To Update Own Device!!!!'], 400);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
}
