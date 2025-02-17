<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Log;

class CarController extends Controller
{
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
        $data = $request->all();

        $mappedData = [
            'name' => $data['full_name'] ?? null,
            'email' => $data['user_mail'] ?? null,
            'contact_number' => $data['phone_number'] ?? null,
        ];

        $item = Car::create($data);
        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        Log::error($id);
        $item = Car::find($id);
        Log::error($request->all());
        if ($item) {
            $item->update($request->all());
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
