<?php

namespace App\Http\Controllers;

use App\Models\CarBrands;
use App\Models\CarTypes;
use App\Models\Facility;
use App\Models\Fav;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Translation\t;

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
        if ($request->has('uid')) {
            $items = Car::where('postId', $request->input('uid'))->select([
                'cars.*',
                DB::raw('(SELECT COUNT(*) FROM gallerys WHERE gallerys.carId = cars.id) AS totalGallery')
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
//        $data = $this->parseRequestParams($request);
        $update_data = $request->all();
        if ($request->input('car_image')) {
            $path = $request->car_image->store('/', 'public');
            $update_data['img'] = $path;
        }
        $item = Car::create($update_data);
        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $item = Car::find($id);
        if ($item) {
//            $update_data = $this->parseRequestParams($request);
            $update_data = $request->all();
            if ($request->input('car_image')) {
                $path = $request->car_image->store('/', 'public');
                $update_data['img'] = $path;
            }
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

        $car = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->find($car_id);
        $averageRating = $car->bookings->isNotEmpty()
            ? $car->bookings->avg('totalRate')
            : $car->car_rating;
        $car->car_rate = number_format($averageRating, 2);

        $facilityResult = Facility::selectRaw('GROUP_CONCAT(title) as facility, GROUP_CONCAT(img) as facility_img')
            ->whereRaw('FIND_IN_SET(id, ?) > 0', [$car["facility"]])->first();

        $t = CarTypes::where('id', $car['type'])->select('img', 'title')->first();
        $b = CarBrands::where('id', $car['brand'])->select('img', 'title')->first();
        Log::info($car);
        $car["img"] = explode('$;',$car["img"]);
        $car["facility"] = $facilityResult["facility"];
        $car["facilityImg"] = $facilityResult["facility_img"];
        $car['typeTitle'] = $t["title"];
        $car['typeImg'] = $t["img"];
        $car['brandTitle'] = $b["title"];
        $car['brandImg'] = $b["img"];
        $car['IS_FAVOURITE'] = Fav::where('uid', $uid)->where('carId', $car['id'])->count();
        $gal = array();
        $gallery = Gallery::where('carId', $car_id)->select('img')->get();
        foreach ($gallery as $rk)
        {
            $gal = explode('$;',$rk->img);
        }
        return response()->json(['carinfo'=> $car, 'Gallery_images'=>$gal, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car info get successfully!'], 200);
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

        $carlists = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($cityid, function ($query) use ($cityid) {
            return $query->where('available', $cityid);
        })->where([
            ['status', 1],
            ['brand', $brand_id],
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
            $im = explode('$;',$car->img);
            $car->img = $im[0];

            return $car;
        })->sortBy('distance')->take(5);

        return response()->json(['FeatureCar'=>$carlists, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Brand Wise Get Successfully!!!'], 200);
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

        $carlists = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($cityid, function ($query) use ($cityid) {
            return $query->where('available', $cityid);
        })->where([
            ['status', 1],
            ['type', $type_id],
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
            $im = explode('$;',$car->img);
            $car->img = $im[0];

            return $car;
        })->sortBy('distance')->take(5);

        return response()->json(['FeatureCar'=>$carlists, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Type Wise Get Successfully!!!'], 200);
    }

    public function featureList(Request $request)
    {
        $userId = Auth::user()->id;
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $cityId = $request->input('city_id');

        $user = User::find($userId);
        $isBlock = empty($user->status) ? '1' : ($user->status == 1 ? '0' : '1');

        $cars = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($cityId, function ($query) use ($cityId) {
            return $query->where('available', $cityId);
        })->where([
            ['status', 1],
            ['postId', '!=', $userId],
            ['isApproved', 1]
        ])->select([
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
        ])->get()->map(function ($car) use ($lats, $longs) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;

            $car->rate = $car_rate;
            $car->distance = $car->calculateDistance($lats, $longs) . ' KM';
            $img = explode('$;',$car->img);
            $car->img = $img[0];

            return $car;
        })->sortBy('distance');

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!', 'is_block' => $isBlock, "tax" => app('set')['tax'], 'currency' => app('set')['currency'], 'FeatureCar' => $cars]);
    }

    public function popularList(Request $request)
    {
        $userId = Auth::user()->id;
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $cityId = $request->input('city_id');

        $user = User::find($userId);
        $isBlock = empty($user->status) ? '1' : ($user->status == 1 ? '0' : '1');

        $cars = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($cityId, function ($query) use ($cityId) {
            return $query->where('available', $cityId);
        })->where([
            ['status', 1],
            ['postId', '!=', $userId],
            ['isApproved', 1]
        ])->select([
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
        ])->get()->map(function ($car) use ($lats, $longs) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;

            $car->rate = $car_rate;
            $car->distance = $car->calculateDistance($lats, $longs) . ' KM';
            $img = explode('$;',$car->img);
            $car->img = $img[0];

            return $car;
        })->sortBy('rating');

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!', 'is_block' => $isBlock, "tax" => app('set')['tax'], 'currency' => app('set')['currency'], 'Recommend_car' => $cars]);
    }
}
