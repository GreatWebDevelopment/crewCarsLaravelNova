<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use App\Models\Banner;
use App\Models\CarTypes;
use App\Models\CarBrands;
use Illuminate\Http\Request;
use illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function get(Request $request)
    {
        if (!$request->has('uid') or $request->input('uid') == '') {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $uid = $request->input('uid');
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $cityid = $request->input('cityid');

        $check_user_verify = User::where('id', $request->input('uid'))->get();
        $is_block = empty($check_user_verify["status"]) ? "1" : ($check_user_verify["status"] == 1 ? "0" : "1");

        $vop =array();
        $ban = array();

        $rp = Banner::where('status', 1)->get();
        while($rp)
        {
            $vop['id'] = $rp['id'];
            $vop['img'] = $rp['img'];
            $ban[] = $vop;
        }

        $pol = array();
        $c = array();
        $row = CarTypes::where('status', 1)->get();
        while($row)
        {
            $pol['id'] = $row['id'];
            $pol['title'] = $row['title'];
            $pol['img'] = $row['img'];
            $c[] = $pol;
        }

        $pols = array();
        $cs = array();
        $rows = CarBrands::where('status', 1)->get();
        while($rows)
        {
            $pols['id'] = $rows['id'];
            $pols['title'] = $rows['title'];
            $pols['img'] = $rows['img'];
            $cs[] = $pols;
        }

        if($request->input('cityid') == 0)
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
                where tcar.car_status=1  and tcar.post_id !=".$uid." and tcar.is_approve=1
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
                where tcar.car_status=1 and tcar.car_available=".$cityid." and tcar.post_id !=".$uid." and tcar.is_approve=1
                order by distance limit 5");
        }

        $navs = array();
        $cars = array();
        while($evs = $carlists)
        {
            $t = CarTypes::where('id', $evs["car_type"])->get();
            $cars["id"] = $evs["id"];
            $cars["car_title"] = $evs["car_title"];
            $im = explode('$;',$evs['car_img']);
            $cars["car_img"] = $im[0];
            $cars["car_rating"] = $evs["car_rate"];
            $cars["car_number"] = $evs["car_number"];
            $cars["total_seat"] = $evs["total_seat"];
            $cars["car_gear"] = $evs["car_gear"];
            $cars["car_rent_price"] = $evs["car_rent_price"];
            $cars["price_type"] = $evs["price_type"];
            $cars["engine_hp"] = $evs["engine_hp"];
            $cars["fuel_type"] = $evs["fuel_type"];
            $cars['car_type_title'] = $t["title"];
            $distance = calculateDistance($lats, $longs,$evs['pick_lat'], $evs['pick_lng'], app('apiKey'));
            $cars["car_distance"] = $distance.' KM';
            $navs[] = $cars;
        }

        if($cityid == 0)
        {
            $carlists = DB::select("SELECT 
                (((acos(sin((".$lats." * pi()/180)) * sin((`pick_lat` * pi()/180)) + cos((".$lats." * pi()/180)) * cos((`pick_lat` * pi()/180)) * cos(((".$longs." - `pick_lng`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) AS distance,
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
            WHERE 
                tcar.car_status = 1 and tcar.post_id !=".$uid." and tcar.is_approve=1
            ORDER BY 
                car_rate limit 5
            ");
        }
        else
        {
            $carlists = DB::select("SELECT 
                (((acos(sin((".$lats."*pi()/180)) * sin((pick_lat*pi()/180))+cos((".$lats."*pi()/180)) * cos((`pick_lat`*pi()/180)) * cos(((".$longs."-`pick_lng`)*pi()/180))))*180/pi())*60*1.1515*1.609344) AS distance,
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
            WHERE 
                tcar.car_status = 1 
                AND tcar.car_available = ".$cityid."  and tcar.post_id !=".$uid." and tcar.is_approve=1
            ORDER BY 
                car_rate limit 5
            ");
        }

        $navsp = array();
        $carss = array();
        while($evs = $carlists->fetch_assoc())
        {

            $t = CarTypes::where('id', $evs["car_type"])->get();
            $carss["id"] = $evs["id"];
            $carss["car_title"] = $evs["car_title"];
            $carss["car_img"] = explode('$;',$evs['car_img']);
            $carss["car_rating"] = $evs["car_rate"];
            $carss["car_number"] = $evs["car_number"];
            $carss["total_seat"] = $evs["total_seat"];
            $carss["car_gear"] = $evs["car_gear"];
            $carss["car_rent_price"] = $evs["car_rent_price"];
            $carss["price_type"] = $evs["price_type"];
            $carss["engine_hp"] = $evs["engine_hp"];
            $carss["fuel_type"] = $evs["fuel_type"];
            $carss['car_type_title'] = $t["title"];
            $distance = calculateDistance($lats, $longs,$evs['pick_lat'], $evs['pick_lng'], app('apiKey'));
            $carss["car_distance"] = $distance.' KM';
            $navsp[] = $carss;

        }

        return response()->json([
            'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!!!',
            'banner'=>$ban, 'is_block'=>$is_block, 'tax'=>$set['tax'], "currency"=>$set['currency'],
            "cartypelist"=>$c, "carbrandlist"=>$cs, "FeatureCar"=>$navs, "Recommend_car"=>$navsp,
            "show_add_car"=>$set['show_add_car']], 200);
    }
}
