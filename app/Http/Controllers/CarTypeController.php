<?php

namespace App\Http\Controllers;

use App\Models\CarTypes;

class CarTypeController extends Controller
{
    public function index()
    {
        $items = CarTypes::where('status', 1)->get();
        return response()->json($items);
    }

    public function show($id)
    {
        $item = CarTypes::find($id);
        if ($item) {
            return response()->json($item);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }
}
