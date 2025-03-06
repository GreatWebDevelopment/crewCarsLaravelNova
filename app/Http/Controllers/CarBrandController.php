<?php

namespace App\Http\Controllers;

use App\Models\CarBrands;

class CarBrandController extends Controller
{
    public function index()
    {
        $items = CarBrands::where('status', 1)->get();
        return response()->json([
            "carbrandlist" => $items,
            "ResponseCode" => "200",
            "Result" => $items->isEmpty() ? "false" : "true",
            "ResponseMsg" => !$items->isEmpty() ? "Car Brand List Founded!" : "Car Brand Not Founded!"
        ]);
    }

    public function show($id)
    {
        $item = CarBrands::find($id);
        if ($item) {
            return response()->json($item);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }
}
