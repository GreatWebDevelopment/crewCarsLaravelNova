<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\PayoutSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index() {
        $uid = Auth::user()->id;
        // Get total number of cars for the user
        $total_car = Car::where('postId', $uid)->count();

        // Get current bookings (not Completed or Cancelled)
        $current_booking = Booking::where('postId', $uid)
            ->whereNotIn('bookingStatus', ['Completed', 'Cancelled'])
            ->count();

        // Get past bookings (Completed or Cancelled)
        $past_booking = Booking::where('postId', $uid)
            ->whereIn('bookingStatus', ['Completed', 'Cancelled'])
            ->count();

        // Calculate total earnings
        $earn = Booking::where('postId', $uid)
            ->where('bookingStatus', 'Completed')
            ->select(DB::raw('SUM((subtotal - couAmt) - ((subtotal - couAmt) * commission / 100)) as total_earning'))
            ->first();

        $earning = $earn->total_earning ?? 0;
        $pay = PayoutSettings::where('uid', $uid)
            ->select(DB::raw('SUM(amt) as total_payout'))
            ->first();
        $payout = $pay->total_payout ?? 0;

        $papi = [
            [
                "title" => "Number of Cars",
                "report_data" => $total_car,
                "url" => "images/dashboard/numberofcar.png",
            ],
            [
                "title" => "Current Booking",
                "report_data" => $current_booking,
                "url" => "images/dashboard/currentbooking.png",
            ],
            [
                "title" => "Past Booking",
                "report_data" => $past_booking,
                "url" => "images/dashboard/passbooking.png",
            ],
            [
                "title" => "Earning",
                "report_data" => $earning - $payout,
                "url" => "images/dashboard/earning.png",
            ],
            [
                "title" => "Payout",
                "report_data" => number_format($payout, 2),
                "url" => "images/dashboard/payout.png",
            ],
            [
                "title" => "Withdraw Limit",
                "report_data" => app('set')->wlimit,
                "url" => "images/dashboard/withdraw-limit.png",
            ],
        ];

        return response()->json([
            "ResponseCode" => "200",
            "Result" => "true",
            "ResponseMsg" => "Report List Get Successfully!!!",
            "report_data" => $papi,
            "Currency" => app('set')->currency,
        ]);
    }
}
