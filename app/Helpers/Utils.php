<?php
// app/Helpers/Utils.php

if (!function_exists('convertToCamelCase')) {
    function convertToCamelCase($string) {
        // Separate the words by underscores
        $words = explode('_', $string);

        if ($words[0] == 'car') {
            array_shift($words);
        }

        // Convert the first word to lowercase
        $camelCaseString = strtolower($words[0]);

        // Capitalize the first letter of each subsequent word and concatenate them
        for ($i = 1; $i < count($words); $i++) {
            $camelCaseString .= ucfirst(strtolower($words[$i]));
        }

        return $camelCaseString;
    }
}

if (!function_exists('calculateDistance')) {
    function calculateDistance($originLat, $originLng, $destLat, $destLng, $apiKey) {
        $unit = "K";
        $theta = (float)$originLng - (float)$destLng;
        $dist = sin(deg2rad((float)$originLat)) * sin(deg2rad((float)$destLat)) + cos(deg2rad((float)$originLat)) * cos(deg2rad((float)$destLat)) * cos(deg2rad((float)$theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            $distanceInKilometers = $miles * 1.609344;
            return round($distanceInKilometers, 2); // Rounded to 2 decimal places
        } else if ($unit == "N") {
            $distanceInNauticalMiles = $miles * 0.8684;
            return round($distanceInNauticalMiles, 2); // Rounded to 2 decimal places
        } else {
            return round($miles, 2); // Rounded to 2 decimal places
        }
    }
}

if (!function_exists('checkRequestParams')) {
    function checkRequestParams($request, $requestParams) {
        foreach ($requestParams as $key) {
            if (!$request->has($key)) {
                return false;
            }
        }
        return true;
    }
}