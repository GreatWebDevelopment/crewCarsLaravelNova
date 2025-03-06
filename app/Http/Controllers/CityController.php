<?php

namespace App\Http\Controllers;

use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        $items = City::where('status', 1)->get();
        return response()->json([
            "citylist" => $items,
            "ResponseCode" => "200",
            "Result" => $items->isEmpty() ? "false" : "true",
            "ResponseMsg" => !$items->isEmpty() ? "City List Founded!" : "City Not Founded!"
        ]);
    }

    public function show($id)
    {
        $item = City::find($id);
        if ($item) {
            return response()->json($item);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }
}
