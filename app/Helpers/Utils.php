<?php
// app/Helpers/Utils.php

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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

if (!function_exists('sendNotification')) {
    function sendNotification($fields)
    {
        $authKey = app('set')->oneHash;

        // Send request using Laravel HTTP Client
        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => "Basic {$authKey}"
        ])->post("https://onesignal.com/api/v1/notifications", $fields);

        return $response->json();
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
    function getDataFromDocument($document) {
        $result = [
            'name' => '',
            'number' => '',
            'issueDate' => null,
            'expireDate' => null,
            'data' => $document,
        ];

        collect($document)->each(function ($item, $key) use (&$result) {
            try {
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
                    $result['issueDate'] = empty($item) ? null : Carbon::parse($item)->format('Y-m-d');
                    return;
                }

                if (str_contains($key, 'expire') || str_contains($key, 'expiration')) {
                    $result['expireDate'] = empty($item) ? null : Carbon::parse($item)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                return;
            }
        });

        return $result;
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($file, $rootPath)
    {
        $url = '';
        $filename = uniqid() . time() . mt_rand() . '.' . $file->getClientOriginalExtension();
        $path = $rootPath . $filename;
        $s3 = Storage::disk('s3')->put($path, file_get_contents($file), 'public');
        if ($s3) {
            $url = $path;
        }

        return $url;
    }
}

if (!function_exists('uploadFiles')) {
    function uploadFiles($files, $rootPath)
    {
        $images = [];
        foreach ($files as $file) {
            $filename = uniqid() . time() . mt_rand() . '.' . $file->getClientOriginalExtension();
            $path = $rootPath . $filename;
            $s3 = Storage::disk('s3')->put($path, file_get_contents($file), 'public');
            if ($s3) {
                $images[] = $path;
            }
        }

        return $images;
    }
}
