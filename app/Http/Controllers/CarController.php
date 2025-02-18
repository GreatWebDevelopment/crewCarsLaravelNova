<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Log;

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
        $items = Car::all();
        return response()->json($items);
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
}
