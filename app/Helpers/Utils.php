<?php

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