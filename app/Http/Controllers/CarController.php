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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use function Pest\Laravel\json;
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

    public function index()
    {
        $items = Car::where('postId', Auth::user()->id)->select([
            'cars.*',
            DB::raw('(SELECT COUNT(*) FROM gallerys WHERE gallerys.carId = cars.id) AS totalGallery')
        ])->get()->makeHidden(['bookings']);
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Cars List Get Successfully!!!', 'mycarlist'=> $items]);
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
        $validator = Validator::make($request->all(), [
            'number' => 'required',
            'status' => 'required',
            'rating' => 'required',
            'seats' => 'required',
            'ac' => 'required',
            'images.*' => 'required|file|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $update_data = $request->all();

        if ($request->hasFile('images')) {
            $images = uploadfiles($request->file('images'), env('CAR_IMAGE_S3_PATH') . 'photos/');
        }

        $update_data['userId'] = Auth::user()->id;
        $update_data['postId'] = Auth::user()->id;
        $update_data['img'] = $images;
        Car::create($update_data);
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Waiting For Approval Car Details']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required',
            'status' => 'required',
            'rating' => 'required',
            'seats' => 'required',
            'ac' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $item = Car::find($id);
        $update_data = $request->all();

        if ($request->hasFile('images')) {
            $images = uploadFiles($request->file('images'), env('CAR_IMAGE_S3_PATH') . 'photos/');
        }

        $existingImages = json_decode($update_data['imlist']);
        $imagesToDelete = array_diff($item->img, $existingImages);
        if (count($imagesToDelete) > 0) {
            foreach ($imagesToDelete as $image) {
                Storage::disk('s3')->delete($image);
            }
        }

        $update_data['img'] = array_merge($existingImages, $images);
        $item->fill($update_data);
        $item->save();
        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Updated Successfully!']);
    }

    public function destroy($id)
    {
        $item = Car::find($id);
        if ($item) {
            foreach ($item->img as $image) {
                Storage::disk('s3')->delete($image);
            }

            $item->delete();
            return response()->json(['message' => 'Item deleted']);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }

    public function info(Request $request)
    {
        if (!$request->has('car_id') or $request->input('car_id') == '') {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }
        $car_id = $request->input('car_id');
        $uid = Auth::user()->id;

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
            $gal = $rk->img;
        }
        return response()->json(['carinfo'=> $car, 'Gallery_images'=>$gal, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car info get successfully!'], 200);
    }

    public function brandWise(Request $request) {
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $brand_id = $request->input('brand_id');
        $location = $request->input('location');
        $uid = Auth::user()->id;

        $carlists = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($location, function ($query) use ($location) {
            return $query->where('location', $location);
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
        )->get()->makeHidden(['bookings'])->map(function ($car) use ($lats, $longs) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;

            $car->rate = $car_rate;
            $car->distance = $car->calculateDistance($lats, $longs).' KM';
            $car->img = $car->img[0];

            return $car;
        })->sortBy('distance')->take(5);

        return response()->json(['FeatureCar'=>$carlists, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Brand Wise Get Successfully!!!'], 200);
    }

    public function typeWise(Request $request) {
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $type_id = $request->input('type_id');
        $location = $request->input('location');
        $uid = Auth::user()->id;

        $carlists = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($location, function ($query) use ($location) {
            return $query->where('location', $location);
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
        )->get()->makeHidden(['bookings'])->map(function ($car) use ($lats, $longs) {
            $bookCount = $car->bookings->count();
            $bookRateSum = $car->bookings->sum('totalRate');

            $car_rate = $bookCount != 0
                ? number_format($bookRateSum / $bookCount, ($bookRateSum % $bookCount > 0) ? 2 : 0)
                : $car->rating;

            $car->rate = $car_rate;
            $car->distance = $car->calculateDistance($lats, $longs).' KM';
            $car->img = $car->img[0];

            return $car;
        })->sortBy('distance')->take(5);

        return response()->json(['FeatureCar'=>$carlists, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Type Wise Get Successfully!!!'], 200);
    }

    public function cityWise(Request $request) {
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $location = $request->input('location');
        $uid = Auth::user()->id;

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

            return $car;
        })->sortBy('distance')->take(5);

        return response()->json(['FeatureCar'=>$carlists, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'City Wise Car Get Successfully!!!'], 200);
    }

    public function featureList(Request $request)
    {
        $userId = Auth::user()->id;
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $location = $request->input('location');

        $user = User::find($userId);
        $isBlock = empty($user->status) ? '1' : ($user->status == 1 ? '0' : '1');

        $cars = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($location, function ($query) use ($location) {
            return $query->where('location', $location);
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
            $car->img = $car->img[0];

            return $car;
        })->sortBy('distance');

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!', 'is_block' => $isBlock, "tax" => app('set')->tax, 'currency' => app('set')->currency, 'FeatureCar' => $cars]);
    }

    public function popularList(Request $request)
    {
        $userId = Auth::user()->id;
        $lats = $request->input('lats');
        $longs = $request->input('longs');
        $location = $request->input('location');

        $user = User::find($userId);
        $isBlock = empty($user->status) ? '1' : ($user->status == 1 ? '0' : '1');

        $cars = Car::with(['bookings' => function ($query) {
            $query->where('bookingStatus', 'Completed')
                ->where('isRate', 1);
        }])->when($location, function ($query) use ($location) {
            return $query->where('location', $location);
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
            $car->img = $car->img[0];

            return $car;
        })->sortBy('rating');

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Home Data Get Successfully!', 'is_block' => $isBlock, "tax" => app('set')->tax, 'currency' => app('set')->currency, 'Recommend_car' => $cars]);
    }
}
