<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use App\Models\WalletReport;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        $get_id = Car::where("id", $request->input('car_id'))->get();
        $wall_amt = strip_tags($request->input('wall_amt'));
        $uid = strip_tags($request->input('uid'));
        $booking = Booking::create([
            'car_id' => strip_tags($request->input('car_id')),
            'uid' => strip_tags($request->input('uid')),
            'car_price' => strip_tags($request->input('car_price')),
            'pickup_date' => strip_tags($request->input('pickup_date')),
            'pickup_time' => strip_tags($request->input('pickup_time')),
            'price_type' => strip_tags($request->input('price_type')),
            'return_date' => strip_tags($request->input('return_date')),
            'return_time' => strip_tags($request->input('return_time')),
            'cou_id' => strip_tags($request->input('cou_id')),
            'cou_amt' => strip_tags($request->input('cou_amt')),
            'wall_amt' => $wall_amt,
            'total_day_or_hr' => strip_tags($request->input('total_day_or_hr')),
            'subtotal' => strip_tags($request->input('subtotal')),
            'tax_per' => strip_tags($request->input('tax_per')),
            'tax_amt' => strip_tags($request->input('tax_amt')),
            'o_total' => strip_tags($request->input('o_total')),
            'p_method_id' => strip_tags($request->input('p_method_id')),
            'transaction_id' => strip_tags($request->input('transaction_id')),
            'type_id' => strip_tags($request->input('type_id')),
            'brand_id' => strip_tags($request->input('brand_id')),
            'book_type' => strip_tags($request->input('book_type')),
            'city_id' => strip_tags($request->input('city_id')),
            'post_id' => $get_id["post_id"],
            'pick_otp' => rand(1111,9999),
            'drop_otp' => rand(1111,9999),
            'commission' => app('set')['commission_rate']
        ]);
        $bookid = $booking->id;

        if($wall_amt != 0)
        {
            $vp = User::where('id', $uid)->get();
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

        $udata = User::where('id', $uid)->get();
        $name = $udata['name'];
        $content = array(
            "en" => $name.', Your car Book #'.$bookid.' Has Been Received.'
        );
        $heading = array(
            "en" => "Book Received!!"
        );

        $fields = array(
            'app_id' => app('set')['one_key'],
            'included_segments' =>  array("Active Users"),
            'data' => array("order_id" =>$bookid),
            'filters' => array(array('field' => 'tag', 'key' => 'user_id', 'relation' => '=', 'value' => $get_id["post_id"])),
            'contents' => $content,
            'headings' => $heading
        );

        $fields = json_encode($fields);
        $headers = [
            'Authorization' => 'Basic '.app('set')['one_hash'],
            'Content-Type' => 'application/json; charset=utf-8'
        ];
        Http::withHeaders($headers)->post('https://onesignal.com/api/v1/notifications', $fields);

        $timestamp = date("Y-m-d H:i:s");
        $title_mains = "Book Received!!";
        $descriptions = $name.', Your car Book #'.$bookid.' Has Been Received.';

        Notification::create([
            "uid" => $get_id["post_id"],
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
        $bookings = Booking::where('car_id', $request->input('car_id'))->get();
        $pol = array();
        $c = array();
        foreach ($bookings as $row)
        {
            $pol['pickup_date'] = $row['pickup_date'];
            $pol['return_date'] = $row['return_date'];
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
        $carinfo = DB::select("select tcar.car_title,tcar.car_number,tcar.car_img,tcar.id,tcar.pick_lat,tcar.pick_lng,tcar.pick_address,tcar.engine_hp,
           tcar.fuel_type,tcar.total_seat,tcar.car_gear,(
                SELECT 
                    CASE 
                        WHEN COUNT(*) != 0 THEN 
                            FORMAT(SUM(total_rate) / COUNT(*), IF(SUM(total_rate) % COUNT(*) > 0, 2, 0))
                        ELSE 
                            tcar.car_rating 
                    END 
                FROM tbl_book 
                WHERE car_id = tcar.id 
                    AND book_status = 'Completed' 
                    AND is_rate = 1
            ) AS car_rate  from tbl_car AS tcar where tcar.id=".$sel['car_id']."");
        $cityinfo = DB::select("select * from tbl_city where id=".$sel['city_id']."");
        $paymentinfo = DB::select("select * from tbl_payment_list where id=".$sel['p_method_id']."");

        $pol['book_id'] = $sel['id'];
        $pol['car_id'] = $sel['car_id'];
        $pol['car_title'] = $carinfo['car_title'];
        $pol['car_number'] = $carinfo['car_number'];
        $pol["car_img"] = $carinfo['car_img'];
        $pol["pick_lat"] = $carinfo['pick_lat'];
        $pol["pick_lng"] = $carinfo['pick_lng'];
        $pol["pick_address"] = $carinfo['pick_address'];
        $pol['city_title'] = $cityinfo['title'];
        $pol['car_rating'] = $carinfo['car_rate'];
        $pol['price_type'] = $sel['price_type'];
        $pol['car_price'] = $sel['car_price'];
        $pol['pickup_date'] = $sel['pickup_date'];
        $pol['pickup_time'] = $sel['pickup_time'];
        $pol['return_date'] = $sel['return_date'];
        $pol['return_time'] = $sel['return_time'];
        $pol['cou_amt'] = $sel['cou_amt'];
        if($sel['post_id'] == 0)
        {
            $pol['owner_name'] = 'admin';
            $pol['owner_contact'] = app('set')['contact_no'];
            $pol['owner_img'] = app('set')['weblogo'];
        }
        else
        {
            $userdata = DB::select("select name,mobile,ccode,profile_pic from tbl_user where id=".$sel['post_id']."");
            $pol['owner_name'] = $userdata['name'];
            $pol['owner_contact'] = $userdata['ccode'].$userdata['mobile'];
            $pol['owner_img'] = $userdata['profile_pic'];
        }
        $pol['book_type'] = $sel['book_type'];
        $pol['wall_amt'] = $sel['wall_amt'];
        $pol['total_day_or_hr'] = $sel['total_day_or_hr'];
        $pol['tax_amt'] = $sel['tax_amt'];
        $pol['tax_per'] = $sel['tax_per'];
        $pol["engine_hp"] = $carinfo["engine_hp"];
        $pol["fuel_type"] = $carinfo["fuel_type"];
        $pol["total_seat"] = $carinfo["total_seat"];
        $pol["car_gear"] = $carinfo["car_gear"];
        $pol['cancle_reason'] = $sel['cancle_reason'];
        $pol['is_rate'] = $sel['is_rate'];
        $pol['subtotal'] = $sel['subtotal'];
        $pol['o_total'] = $sel['o_total'];
        $pol['Payment_method_name'] = $paymentinfo['title'];
        $pol['transaction_id'] = $sel['transaction_id'];
        $pol['book_status'] = $sel['book_status'];
        $pol['exter_photo'] = empty($sel['exter_photo']) ? [] : explode('$;',$sel['exter_photo']);
        $pol['inter_photo'] = empty($sel['inter_photo']) ? [] : explode('$;',$sel['inter_photo']);
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
            $sel = DB::select("select * from tbl_book where uid=".$uid." and book_status!='Cancelled' and book_status!='Completed' order by id desc");
        }
        else
        {
            $sel = DB::select("select * from tbl_book where uid=".$uid." and (book_status='Cancelled' or book_status='Completed') order by id desc");
        }

        foreach ($sel as $row)
        {
            $car_id = $row['car_id'];
            $carinfo = DB::select("select tcar.car_title,tcar.car_number,tcar.car_img,tcar.id,tcar.engine_hp,tcar.fuel_type,tcar.total_seat,tcar.car_gear,(
                SELECT 
                    CASE 
                        WHEN COUNT(*) != 0 THEN 
                            FORMAT(SUM(total_rate) / COUNT(*), IF(SUM(total_rate) % COUNT(*) > 0, 2, 0))
                        ELSE 
                            tcar.car_rating 
                    END 
                FROM tbl_book 
                WHERE car_id = tcar.id 
                    AND book_status = 'Completed' 
                    AND is_rate = 1
            ) AS car_rate  from tbl_car AS tcar where tcar.id=".$car_id."");
            $cityinfo = DB::select("select * from tbl_city where id=".$row['city_id']."");
            $pol['book_id'] = $row['id'];
            $pol['car_title'] = $carinfo['car_title'];
            $pol['car_number'] = $carinfo['car_number'];
            $im = explode('$;',$carinfo['car_img']);
            $pol["car_img"] = $im[0];
            $pol['city_title'] = $cityinfo['title'];
            $pol['car_rating'] = $carinfo['car_rate'];
            $pol['price_type'] = $row['price_type'];
            $pol['car_price'] = $row['car_price'];
            $pol["engine_hp"] = $carinfo["engine_hp"];
            $pol["fuel_type"] = $carinfo["fuel_type"];
            $pol["total_seat"] = $carinfo["total_seat"];
            $pol["car_gear"] = $carinfo["car_gear"];
            $pol['wall_amt'] = $row['wall_amt'];
            $pol['cou_amt'] = $row['cou_amt'];
            $pol['total_day_or_hr'] = $row['total_day_or_hr'];
            $pol['pickup_date'] = $row['pickup_date'];
            $pol['pickup_time'] = $row['pickup_time'];
            $pol['o_total'] = $row['o_total'];
            $pol['return_date'] = $row['return_date'];
            $pol['return_time'] = $row['return_time'];
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

    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    public function update(Request $request, Booking $booking)
    {
        $booking->update($request->all());
        return response()->json($booking);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}