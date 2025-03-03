<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use App\Models\WalletReport;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index()
    {
        return response()->json(Booking::all());
    }

    public function bookNow(Request $request)
    {
        if (!checkRequestParams($request, ['car_id', 'uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $get_id = Car::find($request->input('car_id'));
        $wall_amt = strip_tags($request->input('wall_amt'));
        $uid = strip_tags($request->input('uid'));
        $booking = Booking::create([
            'carId' => strip_tags($request->input('car_id')),
            'uid' => strip_tags($request->input('uid')),
            'carPrice' => strip_tags($request->input('car_price')),
            'pickupDate' => strip_tags($request->input('pickup_date')),
            'pickupTime' => strip_tags($request->input('pickup_time')),
            'priceType' => strip_tags($request->input('price_type')),
            'returnDate' => strip_tags($request->input('return_date')),
            'returnTime' => strip_tags($request->input('return_time')),
            'couId' => strip_tags($request->input('cou_id')),
            'couAmt' => strip_tags($request->input('cou_amt')),
            'wallAmt' => $wall_amt,
            'totalDayOrHr' => strip_tags($request->input('total_day_or_hr')),
            'subtotal' => strip_tags($request->input('subtotal')),
            'taxPer' => strip_tags($request->input('tax_per')),
            'taxAmt' => strip_tags($request->input('tax_amt')),
            'oTotal' => strip_tags($request->input('o_total')),
            'pMethodId' => strip_tags($request->input('p_method_id')),
            'transactionId' => strip_tags($request->input('transaction_id')),
            'typeId' => strip_tags($request->input('type_id')),
            'brandId' => strip_tags($request->input('brand_id')),
            'bookingType' => strip_tags($request->input('book_type')),
            'cityId' => strip_tags($request->input('city_id')),
            'postId' => $get_id["postId"],
            'pickOtp' => rand(1111,9999),
            'dropOtp' => rand(1111,9999),
            'commission' => app('set')->commissionRate
        ]);
        $bookid = $booking->id;

        if($wall_amt != 0)
        {
            $vp = User::find($uid);
            $mt = intval($vp['wallet'])-intval($wall_amt);
            $check = User::where('id', $uid)->update(['wallet' => $mt]);
            $tdate = date("Y-m-d");

            $checks = WalletReport::create([
                "uid" => $uid,
                "message" => 'Wallet Used in Rent Id#'.$bookid,
                "status" => 'Debit',
                "amt" => $wall_amt,
                "tdate" => $tdate
            ]);
        }

        $udata = User::find('id', $uid);
        $name = $udata['name'];
        $content = array(
            "en" => $name.', Your car Book #'.$bookid.' Has Been Received.'
        );
        $heading = array(
            "en" => "Book Received!!"
        );

        $fields = array(
            'app_id' => app('set')->oneKey,
            'included_segments' =>  array("Active Users"),
            'data' => array("order_id" =>$bookid),
            'filters' => array(array('field' => 'tag', 'key' => 'user_id', 'relation' => '=', 'value' => $get_id["post_id"])),
            'contents' => $content,
            'headings' => $heading
        );

        $fields = json_encode($fields);
        $headers = [
            'Authorization' => 'Basic '.app('set')->oneHash,
            'Content-Type' => 'application/json; charset=utf-8'
        ];
        Http::withHeaders($headers)->post('https://onesignal.com/api/v1/notifications', $fields);

        $timestamp = date("Y-m-d H:i:s");
        $title_mains = "Book Received!!";
        $descriptions = $name.', Your car Book #'.$bookid.' Has Been Received.';

        Notification::create([
            "uid" => $get_id["postId"],
            "datetime" => $timestamp,
            "title" => $title_mains,
            "description" => $descriptions
        ]);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Book Car Done Successfully!'], 200);
    }

    public function bookRange(Request $request) {
        if (!checkRequestParams($request, ['car_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $bookings = Booking::where('carId', $request->input('car_id'))->get();
        $pol = array();
        $c = array();
        foreach ($bookings as $row)
        {
            $pol['pickupDate'] = $row['pickupDate'];
            $pol['returnDate'] = $row['returnDate'];
            $c[] = $pol;
        }
        if (empty($c))
        {
            return response()->json(['bookeddate'=>$c, 'ResponseCode' => '200', 'Result' => 'false', 'ResponseMsg' => 'Date Not Founded!'], 200);
        }
        else
        {
            return response()->json(['bookeddate'=>$c, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Date List Founded!'], 200);
        }
    }

    public function bookDetails(Request $request) {
        if (checkRequestParams($request, ['uid', 'book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $pol = array();
        $c = array();
        $uid = $request->input('uid');
        $book_id = $request->input('book_id');

        $sel = Booking::where('uid', $uid)->where('id', $book_id)->get();

        $carinfo = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->where('id', $sel['carId'])->select(
            'id',
            'title',
            'img',
            'rating',
            'number',
            'seats',
            'transmission',
            'pickLat',
            'pickLng',
            'pickAddress',
            'rentPrice',
            'priceType',
            'engineHp',
            'fuelType',
            'type'
        )->get()->map(function ($car) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;
            $car->rate = $car_rate;

            $im = explode('$;',$car->img);
            $car->img = $im[0];

            return $car;
        });

        $cityinfo = City::find($sel['cityId']);
        $paymentinfo = PaymentMethod::find($sel['pMethodId']);

        $pol = collect($carinfo)->merge($sel);
        $pol['id'] = $carinfo['id'];
        $pol['bookId'] = $sel['id'];
        $pol['carRating'] = $carinfo['rate'];
        if($sel['postId'] == 0)
        {
            $pol['ownerName'] = 'admin';
            $pol['ownerContact'] = app('set')->contactNo;
            $pol['ownerImg'] = app('set')->weblogo;
        }
        else
        {
            $userdata = User::find($sel['postId'])->select('name','mobile','countryCode','profilePicture');
            $pol['ownerName'] = $userdata['name'];
            $pol['ownerContact'] = $userdata['countryCode'].$userdata['mobile'];
            $pol['ownerImg'] = $userdata['profilePicture'];
        }
        $pol['cityTitle'] = $cityinfo['title'];
        $pol['paymentMethodName'] = $paymentinfo['title'];
        $pol['exterPhoto'] = empty($sel['exterPhoto']) ? [] : explode('$;',$sel['exterPhoto']);
        $pol['interPhoto'] = empty($sel['interPhoto']) ? [] : explode('$;',$sel['interPhoto']);
        $c[] = $pol;

        if(empty($c))
        {
            return response()->json(['book_details'=>$c, 'ResponseCode' => '200', 'Result' => 'false', 'ResponseMsg' => 'Book Details Not Founded!'], 200);
        }
        else
        {
            return response()->json(['book_details'=>$c, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Book Details Founded!'], 200);
        }
    }

    public function bookHistory(Request $request)
    {
        if (checkRequestParams($request, ['uid', 'status'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $pol = array();
        $c = array();
        $uid = $request->input('uid');
        $status = $request->input('status');
        if($status == 'Booked')
        {
            $sel = Booking::where([
                ['uid', $uid],
                ['bookingStatus', '!=', 'Cancelled'],
                ['bookingStatus', '!=', 'Completed']
            ])->orderBy('id', 'desc')->get();
        }
        else
        {
            $sel = Booking::where('uid', $uid)
                ->where(function ($query) {
                    $query->where('bookingStatus', 'Cancelled')
                        ->orWhere('bookingStatus', 'Completed');
                })
                ->orderBy('id', 'desc')
                ->get();
        }

        foreach ($sel as $row)
        {
            $car_id = $row['carId'];
            $carinfo = Car::with(['bookings' => function ($query) {
                $query->where('bookingStatus', 'Completed')
                    ->where('isRate', 1);
            }])->where('id', $car_id)->select(
                'id',
                'title',
                'img',
                'rating',
                'number',
                'seats',
                'transmission',
                'engineHp',
                'fuelType',
            )->get()->map(function ($car) {
                $bookCount = $car->bookings->count();
                $bookRateSum = $car->bookings->sum('totalRate');

                $car_rate = $bookCount != 0
                    ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                    : $car->rating;
                $car->rate = $car_rate;

                $im = explode('$;',$car->img);
                $car->img = $im[0];

                return $car;
            });

            $cityinfo = City::find($row['cityId']);
            $pol = collect($carinfo)->merge($row);
            $pol['id'] = $carinfo['id'];
            $pol['book_id'] = $row['id'];
            $pol['cityTitle'] = $cityinfo['title'];
            $c[] = $pol;
        }
        if(empty($c))
        {
            return response()->json(["book_history"=>$c, 'ResponseCode' => '200', 'Result' => 'false', 'ResponseMsg' => 'Book History Not Founded!'], 200);
        }
        else
        {
            return response()->json(["book_history"=>$c, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Book History List Founded!'], 200);
        }
    }

    public function myBookHistory(Request $request) {
        Log::info($request->all());
        if (checkRequestParams($request, ['uid', 'status'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $uid = $request->input('uid');
        $status = $request->input('status');

        $query = Booking::with(['car', 'city'])
            ->where('postId', $uid)
            ->orderBy('id', 'desc');

        if ($status == 'Booked') {
            $query->whereNotIn('bookingStatus', ['Cancelled', 'Completed']);
        } else {
            $query->whereIn('bookingStatus', ['Cancelled', 'Completed']);
        }

        $bookings = $query->get();
        $c = [];
        $pol = [];

        foreach ($bookings as $row) {
            if (!$row->car) continue; // Skip if car info is missing

            $car = $row->car;
            $city = $row->city;

            $pol[] = collect($car)->merge($row);
            $pol['bookId'] = $row->id;
            $pol['img'] = explode('$;', $car->img)[0];
            $pol['city_title'] = optional($city)->title;
            $c[] = $pol;
        }

        return response()->json([
            "book_history" => $c,
            "ResponseCode" => "200",
            "Result" => !empty($c) ? "true" : "false",
            "ResponseMsg" => !empty($c) ? "Book History List Founded!" : "Book History Not Founded!"
        ]);

    }

    public function myBookDetails(Request $request) {
        if (checkRequestParams($request, ['uid', 'book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $uid = $request->input('uid');
        $book_id = $request->input('book_id');

        $bookings = Booking::with(['car', 'city'])
            ->where('postId', $uid)->where('id', $book_id)
            ->orderBy('id', 'desc')->first();

        $c = [];
        $pol = [];

        $car = $bookings->car;
        $user = $bookings->user;

        $pol[] = collect($car)->merge($bookings);
        $pol['bookId'] = $bookings->id;
        $pol['img'] = explode('$;', $car->img)[0];
        $pol['cityTitle'] = optional($bookings->city)->title;
        $pol['paymentMethodName'] = optional($bookings->paymentMethod)->title;
        $pol['customerName'] = $user['name'];
        $pol['customerContact'] = $user['countryCode'].$user['mobile'];
        $pol['customerImg'] = $user['profilePicture'];
        $pol['exterPhoto'] = empty($bookings['exter_photo']) ? [] : explode('$;',$bookings['exter_photo']);
        $pol['interPhoto'] = empty($bookings['inter_photo']) ? [] : explode('$;',$bookings['inter_photo']);

        $c[] = $pol;

        return response()->json([
            "book_details" => $c,
            "ResponseCode" => "200",
            "Result" => !empty($c) ? "true" : "false",
            "ResponseMsg" => !empty($c) ? "Book Details Founded!" : "Book Details Not Founded!"
        ]);
    public function bookDrop(Request $request)
    {
        if (!checkRequestParams($request, ['book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $userId = Auth::user()->id;
        $bookId = $request->input('book_id');

        $user = User::find($userId);
        $booking = Booking::find($bookId);
        $booking->bookingStatus = 'Drop';
        $booking->userId = $userId;
        $booking->save();


        $fields = json_encode([
            'app_id' => app('set')['oneKey'],
            'included_segments' => ['Active Users'],
            'data' => ['order_id' => $bookId],
            'filters' => [['field' => 'tag', 'key' => 'user_id', 'relation' => '=', 'value' => $booking->postId]],
        ]);

        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . app('set')['oneHash']
        ];

        Http::withHeaders($headers)->post('https://onesignal.com/api/v1/notifications', $fields);

        $title = 'Car Drop!';
        $description = $user->name . ', Your Car Drop.';
        $timestamps = date('Y-m-d H:i:s');

        Notification::create([
            "uid" => $booking->postId,
            "datetime" => $timestamps,
            "title" => $title,
            "description" => $description
        ]);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Drop Successfully!']);
    }

    public function bookCancel(Request $request)
    {
        if (!checkRequestParams($request, ['book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $userId = Auth::user()->id;
        $bookId = $request->input('book_id');
        $reason = $request->input('cancel_reason');

        $booking = Booking::find($bookId);
        $booking->userId = $userId;
        $booking->bookingStatus = 'Cancelled';
        $booking->cancelReason = $reason;
        $booking->save();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Booking Cancelled Successfully!']);
    }

    public function verifyOTP(Request $request)
    {
        if (!checkRequestParams($request, ['status', 'otp', 'book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $userId = Auth::user()->id;
        $bookId = $request->input('book_id');
        $status = $request->input('status');
        $otp = $request->input('otp');

        if ($status == 'Pickup') {
            $bookings = Booking::where('id', $bookId)
                ->where('userId', $userId)
                ->where('pickOtp', $otp)
                ->get();

            if (count($bookings) > 0) {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Otp Matched!']);
            } else {
                return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Otp Not Matched!'], 401);
            }
        } else {
            $bookings = Booking::where('id', $bookId)
                ->where('userId', $userId)
                ->where('dropOtp', $otp)
                ->get();

            if (count($bookings) > 0) {
                return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Otp Matched!']);
            } else {
                return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Otp Not Matched!'], 401);
            }
        }
    }

    public function rateList($id)
    {
        $bookings = Booking::where('carId', $id)
            ->where('bookingStatus', 'Completed')
            ->where('isRate', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($booking) {
                $user = User::find($booking->userId);
                return [
                    'user_img' => empty($user->profilePicture) ? '' : $user->profilePicture,
                    'user_title' => $booking->name,
                    'user_rate' => $booking->totalRate,
                    'review_date' => $booking->reviewDate,
                    'user_desc' => $booking->rateText,
                ];
            });

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Review Data Get Successfully!', 'reviewdata' => $bookings]);
    }

    public function updateRate(Request $request, $id)
    {
        if (!checkRequestParams($request, ['total_rate', 'rate_text'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $totalRate = $request->input('total_rate');
        $rateText = $request->input('rate_text');
        $timestamps = date('Y-m-d H:i:s');

        $booking = Booking::find($id);
        if (!empty($booking) && $booking->bookingStatus == 'Completed') {
            $booking->totalRate = $totalRate;
            $booking->rateText = $rateText;
            $booking->isRate = 1;
            $booking->reviewDate = $timestamps;
            $booking->save();

            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Rate Updated Successfully!']);
        } else {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Car Not Drop Original Locations'], 401);
        }
    }

    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    public function update(Request $request)
    {
        if (checkRequestParams($request, ['book_id'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $book_id = $request->input('book_id');

        $booking = Booking::find($book_id);
        $booking->bookingStatus = 'Completed';
        $booking->save();

        $user = $booking->user;

        $fields = [
            'app_id' => app('set')->oneKey,
            'included_segments' => ["Active Users"],
            'data' => ["order_id" => $book_id],
            'filters' => [
                ['field' => 'tag', 'key' => 'user_id', 'relation' => '=', 'value' => $booking->uid]
            ],
            'contents' => [
                "en" => "{$user->name}, Your Book #{$book_id} Has Been Completed."
            ],
            'headings' => [
                "en" => "Book Completed!!"
            ]
        ];
        sendNotification($fields);

        return response()->json(["ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Book Complete Successfully!"]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}