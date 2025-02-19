<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VinDecoderController extends Controller
{
    /**
     * Decode the given VIN using the NHTSA API.
     */
    public function decodeVin($vin)
    {
        // Validate VIN format
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
            return response()->json(['error' => 'Invalid VIN format'], 400);
        }

        // Call the NHTSA API
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVin/{$vin}?format=json";
        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch VIN data'], 500);
        }

        $data = $response->json();
        $decodedData = [];

        // Extract meaningful fields
        foreach ($data['Results'] as $item) {
            if (!empty($item['Value'])) {
                $decodedData[$item['Variable']] = $item['Value'];
            }
        }

        return response()->json($decodedData);
    }
}
