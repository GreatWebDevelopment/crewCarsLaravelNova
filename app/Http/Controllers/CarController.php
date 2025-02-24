<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    private $field_values = [
        "car_number",
        "car_status",
        "car_title",
        "car_rating",
        "total_seat",
        "car_ac",
        "driver_name",
        "driver_mobile",
        "car_img",
        "car_gear",
        "car_facility",
        "car_type",
        "car_brand",
        "car_available",
        "car_rent_price",
        "car_rent_price_driver",
        "engine_hp",
        "price_type",
        "fuel_type",
        "car_desc",
        "pick_address",
        "pick_lat",
        "pick_lng",
        "total_km",
        "post_id",
        "min_hrs",
        "img"
    ];

    private function parseRequestParams($request) {
        $update_data = [];
        for ($i = 0; $i < count($this->field_values); $i++) {
            $field = $this->field_values[$i];
            $column = '';
            if ($request->input($field)) {
                if ($field == "img") {
                    $column = 'img';
                } elseif ($field == "total_seat") {
                    $column = 'seats';
                } elseif ($field == "car_gear") {
                    $column = 'transmission';
                } elseif ($field == "car_desc") {
                    $column = 'description';
                } elseif ($field == "total_km") {
                    $column = 'totalMiles';
                } else {
                    $column = convertToCamelCase($field);
                }
                $update_data[$column] = $request->input($field);
            }
        }
        if ($request->input('car_image')) {
            $path = $request->car_image->store('/', 'public');
            $update_data['img'] = $path;
        }

        return $update_data;
    }

    public function index(Request $request)
    {
        Log::info($request->all());
        if ($request->has('uid')) {
            $items = Car::where('postId', $request->input('uid'))->select([
                'cars.*',
                DB::raw('(SELECT COUNT(*) FROM gallerys WHERE gallerys.carId = cars.id) AS total_gallery')
            ])->get();
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Type wise Car Get Successfully!!!', 'mycarlist'=> $items], 200);
        } else {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
    }

    public function show($id)
    {
        $item = Car::find($id);
        if ($item) {
            return response()->json($item);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $data = $this->parseRequestParams($request);
        Log::info($data);
        $item = Car::create($data);
        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
//        \Log::info($request->input('car_number'));
        dd($request->all());
        $item = Car::find($id);
        if ($item) {
            $update_data = $this->parseRequestParams($request);
            $item->fill($update_data);
            $item->save();
            return response()->json($item);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }

    public function destroy($id)
    {
        $item = Car::find($id);
        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Item deleted']);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }

    public function info(Request $request)
    {
        if (!$request->has('uid') or !$request->has('car_id') or $request->input('uid') == '' or $request->input('car_id') == '') {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $car_id = $request->input('car_id');
        $uid = $request->input('uid');

        $carlists = DB::select("select tcar.*,(
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
        ) AS car_rate  from tbl_car AS tcar where tcar.id= ?", [$car_id]);

        $facilityResult = DB::select("SELECT GROUP_CONCAT(title) as facility,GROUP_CONCAT(img) as facility_img
            FROM tbl_facility 
            WHERE FIND_IN_SET(tbl_facility.id, ?) > 0", [$carlists["car_facility"]]);

        $t = DB::select("select img,title from car_type where id= ?", [$carlists["car_type"]]);
        $b = DB::select("select img,title from car_brand where id= ?", [$carlists["car_brand"]]);
        $cars = array();
        $cars["id"] = $carlists["id"];
        $cars["type_id"] = $carlists["car_type"];
        $cars["city_id"] = $carlists["car_available"];
        $cars["brand_id"] = $carlists["car_brand"];
        $cars["min_hrs"] = $carlists["min_hrs"];
        $cars["car_title"] = $carlists["car_title"];
        $cars["car_img"] = explode('$;',$carlists["car_img"]);
        $cars["car_rating"] = $carlists["car_rate"];
        $cars["car_number"] = $carlists["car_number"];
        $cars["total_seat"] = $carlists["total_seat"];
        $cars["car_gear"] = $carlists["car_gear"];
        $cars["total_km"] = $carlists["total_km"];
        $cars["pick_lat"] = $carlists["pick_lat"];
        $cars["pick_lng"] = $carlists["pick_lng"];
        $cars["pick_address"] = $carlists["pick_address"];
        $cars["car_desc"] = $carlists["car_desc"];
        $cars["fuel_type"] = $carlists["fuel_type"];
        $cars["price_type"] = $carlists["price_type"];
        $cars["engine_hp"] = $carlists["engine_hp"];
        $cars["car_facility"] = $facilityResult["facility"];
        $cars["facility_img"] = $facilityResult["facility_img"];
        $cars['car_type_title'] = $t["title"];
        $cars['car_type_img'] = $t["img"];
        $cars['car_brand_title'] = $b["title"];
        $cars['car_brand_img'] = $b["img"];
        $cars["car_rent_price"] = $carlists["car_rent_price"];
        $cars["car_rent_price_driver"] = $carlists["car_rent_price_driver"];
        $cars["car_ac"] = $carlists["car_ac"];
        $cars['IS_FAVOURITE'] = count(DB::select("select * from tbl_fav where uid=? and car_id=?", [$uid, $carlists['id']]));
        $gal = array();
        $gallery = DB::select("select img from tbl_gallery where car_id=?", [$car_id]);
        foreach ($gallery as $rk)
        {
            $gal = explode('$;',$rk->img);
        }

        return response()->json(['carinfo'=> $cars, 'Gallery_images'=>$gal, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car info get successfully!'], 200);
    }

    public function brandWise(Request $request) {
        if (!checkRequestParams($request, ['uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $brand_id = $request->input('brand_id');
        $cityid = $request->input('cityid');
        $uid = $request->input('uid');

        if($cityid == 0)
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
            where tcar.car_status=1 and tcar.car_brand=".$brand_id." and tcar.post_id !=".$uid." and tcar.is_approve=1
            order by distance limit 5");
        }
        else
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
            where tcar.car_status=1 and tcar.car_available=".$cityid." and tcar.car_brand=".$brand_id." and tcar.post_id !=".$uid." and tcar.is_approve=1
            order by distance limit 5");
        }

        $navs = array();
        $car = array();
        foreach ($carlists as $evs)
        {
            $car["id"] = $evs["id"];
            $car["car_title"] = $evs["car_title"];
            $im = explode('$;',$evs['car_img']);
            $car["car_img"] = $im[0];
            $car["car_rating"] = $evs["car_rate"];
            $car["car_number"] = $evs["car_number"];
            $car["total_seat"] = $evs["total_seat"];
            $car["car_gear"] = $evs["car_gear"];
            $car["car_rent_price"] = $evs["car_rent_price"];
            $car["price_type"] = $evs["price_type"];
            $car["engine_hp"] = $evs["engine_hp"];
            $car["fuel_type"] = $evs["fuel_type"];
            $distance = calculateDistance($lats, $longs,$evs['pick_lat'], $evs['pick_lng'], app('apiKey'));
            $car["car_distance"] = $distance.' KM';
            $navs[] = $car;
        }
        return response()->json(['FeatureCar'=>$navs, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Brand Wise Get Successfully!!!'], 200);
    }

    public function typeWise(Request $request) {
        if (!checkRequestParams($request, ['uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $type_id = $request->input('type_id');
        $cityid = $request->input('cityid');
        $uid = $request->input('uid');

        if($cityid == 0)
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
                where tcar.car_status=1 and tcar.car_type=".$type_id." and tcar.post_id !=".$uid." and tcar.is_approve=1
                order by distance limit 5");
        }
        else
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
                where tcar.car_status=1 and tcar.car_available=".$cityid." and tcar.car_type=".$type_id." and tcar.post_id !=".$uid." and tcar.is_approve=1
                order by distance limit 5");
        }

        $navs = array();
        $car = array();
        foreach ($carlists as $evs)
        {
            $car["id"] = $evs["id"];
            $car["car_title"] = $evs["car_title"];
            $car["car_img"] = $evs["car_img"];
            $car["car_rating"] = $evs["car_rate"];
            $car["car_number"] = $evs["car_number"];
            $car["total_seat"] = $evs["total_seat"];
            $car["car_gear"] = $evs["car_gear"];
            $car["car_rent_price"] = $evs["car_rent_price"];
            $car["price_type"] = $evs["price_type"];
            $car["engine_hp"] = $evs["engine_hp"];
            $car["fuel_type"] = $evs["fuel_type"];
            $distance = calculateDistance($lats, $longs,$evs['pick_lat'], $evs['pick_lng'], app('apiKey'));
            $car["car_distance"] = $distance.' KM';
            $navs[] = $car;
        }
        return response()->json(['FeatureCar'=>$navs, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Type Wise Get Successfully!!!'], 200);
    }
}
