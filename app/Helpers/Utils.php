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

if (!function_exists("checkRequestParams")) {
    function checkRequestParams($request, $requestParams)
    {
        foreach ($requestParams as $requestParam) {
            if (!$request->has($requestParam) || empty($request->input($requestParam))) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists("getNameFieldFromDoc")) {
    function getNameFieldFromDoc($document) {
        foreach ($document as $key => $value) {
            $key = strtolower($key);
            if (str_contains($key, 'name')) {
                return $value;
            }
        }
        return '';
    }
}

if (!function_exists("getNumberFieldFromDoc")) {
    function getNumberFieldFromDoc($document) {
        foreach ($document as $key => $value) {
            $key = strtolower($key);
            if (str_contains($key, 'number')) {
                if (str_contains($key, 'policy') || str_contains($key, 'document') || str_contains($key, 'certificate')) {
                    return $value;
                }
            }
        }
        return '';
    }
}

if (!function_exists("getIssueDateFieldFromDoc")) {
    function getIssueDateFieldFromDoc($document) {
        foreach ($document as $key => $value) {
            $key = strtolower($key);
            if (str_contains($key, 'issue') || str_contains($key, 'effect')) {
                return $value;
            }
        }
        return '';
    }
}

if (!function_exists("getExpireDateFieldFromDoc")) {
    function getExpireDateFieldFromDoc($document) {
        foreach ($document as $key => $value) {
            $key = strtolower($key);
            if (str_contains($key, 'expire')) {
                return $value;
            }
        }
        return '';
    }
}

if (!function_exists("getDataFromDocument")) {
    function getDataFromDocument($document, $type) {
        $result = [
            'name' => '',
            'number' => '',
            'issueDate' => '',
            'expireDate' => '',
            'type' => $type,
            'data' => $document,
        ];

        collect($document)->each(function ($item, $key) {
            $key = strtolower($key);
            if (str_contains($key, 'name')) {
                $result['name'] = $item;
                return;
            }

            if (str_contains($key, 'number')) {
                if (str_contains($key, 'policy') || str_contains($key, 'document') || str_contains($key, 'certificate')) {
                    $result['number'] = $item;
                    return;
                }
            }

            if (str_contains($key, 'issue') || str_contains($key, 'effect')) {
                $result['issueDate'] = $item;
                return;
            }

            if (str_contains($key, 'expire')) {
                $result['expireDate'] = $item;
            }
        });

        return $result;
    }
}