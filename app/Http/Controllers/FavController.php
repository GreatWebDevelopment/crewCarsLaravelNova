<?php

namespace App\Http\Controllers;

use App\Models\Car;
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
        foreach ($getfavlist as $row)
        {
            $carlists = Car::with(['bookings' => function ($query) {
                $query->where('bookingStatus', 'Completed')
                    ->where('isRate', 1);
            }])->where([
                ['status', 1],
                ['postId', '!=', $uid],
                ['id', $row['carId']]
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
                $im = explode('$;',$car->img);
                $car->img = $im[0];

                return $car;
            })->sortBy('distance');

            $navs[] = $carlists;
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
