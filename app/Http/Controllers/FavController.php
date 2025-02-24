<?php

namespace App\Http\Controllers;

use App\Models\Fav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavController extends Controller
{
    public function index(Request $request)
    {
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $uid = $request->input('uid');

        if($uid == '')
        {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false',
                'ResponseMsg' => 'Something Went wrong!'], 401);
        }

        $getfavlist = Fav::where('uid', $uid)->get();
        $navs = array();
        $cars = array();
        foreach ($getfavlist as $row)
        {
            $carlists = DB::select("SELECT 
            (((acos(sin((".$lats."*pi()/180)) * sin((`pick_lat`*pi()/180))+cos((".$lats."*pi()/180)) * cos((`pick_lat`*pi()/180)) * cos(((".$longs."-`pick_lng`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance,
            tcar.id,
            tcar.car_title,
            tcar.car_img,
            tcar.car_rating,
            tcar.car_number,
            tcar.total_seat,
            tcar.car_gear,
            tcar.pick_lat,
            tcar.pick_lng,
            tcar.car_rent_price,
            tcar.price_type,
            tcar.engine_hp,
            tcar.fuel_type,
            tcar.car_type,
            (
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
            ) AS car_rate 
        FROM 
            tbl_car AS tcar
            where tcar.car_status=1 and  tcar.id=".$row["car_id"]." and tcar.post_id !=".$uid."
            order by distance");

            $cars["id"] = $carlists["id"];
            $cars["car_title"] = $carlists["car_title"];
            $cars["car_img"] = $carlists["car_img"];
            $cars["car_rating"] = $carlists["car_rate"];
            $cars["car_number"] = $carlists["car_number"];
            $cars["total_seat"] = $carlists["total_seat"];
            $cars["car_gear"] = $carlists["car_gear"];
            $cars["price_type"] = $carlists["price_type"];
            $cars["engine_hp"] = $carlists["engine_hp"];
            $cars["fuel_type"] = $carlists["fuel_type"];
            $distance = calculateDistance($lats, $longs,$carlists['pick_lat'], $carlists['pick_lng'], app('apiKey'));
            $cars["car_distance"] = $distance.' KM';
            $navs[] = $cars;
        }
        return response()->json(['FeatureCar'=> $navs, 'ResponseCode' => '200', 'Result' => 'true',
            'ResponseMsg' => 'Favourite Car Get Successfully!'], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $uid = $request->input('uid');
        $car_id = $request->input('car_id');
        if($uid == '' or $car_id == '')
        {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false',
                'ResponseMsg' => 'Something Went wrong, try again!'], 401);
        }
        $check = Fav::where('uid', $uid)->where('car_id', $car_id)->count();
        if ($check != 0)
        {
            Fav::where('uid', $uid)->where('car_id', $car_id)->delete();
            return response()->json(['ResponseCode' => '200', 'Result' => 'true',
                'ResponseMsg' => 'Car Successfully Removed In Favourite List!'], 200);
        } else {
            DB::table('tbl_fav')->insert([
                'uid' => $uid,
                'car_id' => $car_id,
            ]);
            return response()->json(['ResponseCode' => '200', 'Result' => 'true',
                'ResponseMsg' => 'Car Successfully Saved In Favourite List!'], 200);
        }
    }
}
