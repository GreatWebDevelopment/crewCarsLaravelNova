<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use App\Models\Banner;
use App\Models\CarTypes;
use App\Models\CarBrands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class HomeController extends Controller
{
    public function get(Request $request)
    {
        $uid = Auth::user()->id;
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $location = $request->input('location');

        $check_user_verify = User::find($uid);
        $is_block = empty($check_user_verify["status"]) ? "1" : ($check_user_verify["status"] == 1 ? "0" : "1");

        $vop =array();
        $ban = array();

        $ban = Banner::where('status', 1)->select('id', 'img')->get();

        $c = CarTypes::where('status', 1)->select('id', 'title', 'img')->get();

        $cs = CarBrands::where('status', 1)->select('id', 'title', 'img')->get();

        $carlists = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($location, function ($query) use ($location) {
            return $query->where('location', $location);
        })->where([
            ['status', 1],
            ['postId', '!=', $uid],
            ['isApproved', 1]
        ])->select(
            'id',
            'title',
            'img',
            'rating',
            'number',
            'seats',
            'transmission',
            'pickLat',
            'pickLng',
            'rentPrice',
            'priceType',
            'engineHp',
            'fuelType',
            'type'
        )->get()->map(function ($car) use ($lats, $longs) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;

            $car->rate = $car_rate;
            $car->distance = $car->calculateDistance($lats, $longs).' KM';
            $car->img = $car->img[0];
            $car->typeTitle = $car->typeData->title;

            return $car;
        });
        $navs = $carlists->sortBy('distance')->take(5);
        $navsp = $carlists->sortBy('rate')->take(5);

        return response()->json([
            'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!!!',
            'banner'=>$ban, 'is_block'=>$is_block, 'tax'=>app('set')->tax, "currency"=>app('set')->currency,
            "cartypelist"=>$c, "carbrandlist"=>$cs, "FeatureCar"=>$navs, "Recommend_car"=>$navsp,
            "show_add_car"=>app('set')->showAddCar], 200);
    }
}
