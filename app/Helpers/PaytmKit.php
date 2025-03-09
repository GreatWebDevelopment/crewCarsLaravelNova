<?php

if (!function_exists('encrypt_e')) {
    function encrypt_e($input, $ky)
    {
        $key = html_entity_decode($ky);
        $iv = '@@@@&&&&####$$$$';
        $data = openssl_encrypt($input, 'AES-128-CBC', $key, 0, $iv);
        return $data;
    }
}

if (!function_exists('decrypt_e')) {
    function decrypt_e($crypt, $ky)
    {
        $key = html_entity_decode($ky);
        $iv = "@@@@&&&&####$$$$";
        $data = openssl_decrypt($crypt, "AES-128-CBC", $key, 0, $iv);
        return $data;
    }
}

if (!function_exists('generateSalt_e')) {
    function generateSalt_e($length)
    {
        $random = '';
        srand((double)microtime() * 1000000);

        $data = 'AbcDE123IJKLMN67QRSTUVWXYZ';
        $data .= 'aBCdefghijklmn123opq45rs67tuv89wxyz';
        $data .= '0FGH45OP89';

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }

        return $random;
    }
}

if (!function_exists('checkString_e')) {
    function checkString_e($value)
    {
        if ($value == 'null')
            $value = '';
        return $value;
    }
}

if (!function_exists('getChecksumFromArray')) {
    function getChecksumFromArray($arrayList, $key, $sort = 1)
    {
        if ($sort != 0) {
            ksort($arrayList);
        }
        $str = getArray2Str($arrayList);
        $salt = generateSalt_e(4);
        $finalString = $str . '|' . $salt;
        $hash = hash('sha256', $finalString);
        $hashString = $hash . $salt;
        $checksum = encrypt_e($hashString, $key);
        return $checksum;
    }
}

if (!function_exists('getArray2Str')) {
    function getArray2Str($arrayList)
    {
        $findme = 'REFUND';
        $findmepipe = '|';
        $paramStr = '';
        $flag = 1;
        foreach ($arrayList as $value) {
            $pos = strpos($value, $findme);
            $pospipe = strpos($value, $findmepipe);
            if ($pos !== false || $pospipe !== false) {
                continue;
            }

            if ($flag) {
                $paramStr .= checkString_e($value);
                $flag = 0;
            } else {
                $paramStr .= '|' . checkString_e($value);
            }
        }
        return $paramStr;
    }
}

if (!function_exists('verifychecksum_e')) {
    function verifychecksum_e($arrayList, $key, $checksumvalue)
    {
        $arrayList = removeCheckSumParam($arrayList);
        ksort($arrayList);
        $str = getArray2StrForVerify($arrayList);
        $paytm_hash = decrypt_e($checksumvalue, $key);
        $salt = substr($paytm_hash, -4);

        $finalString = $str . '|' . $salt;

        $website_hash = hash('sha256', $finalString);
        $website_hash .= $salt;

        $validFlag = 'FALSE';
        if ($website_hash == $paytm_hash) {
            $validFlag = 'TRUE';
        } else {
            $validFlag = 'FALSE';
        }
        return $validFlag;
    }
}

if (!function_exists('getArray2StrForVerify')) {
    function getArray2StrForVerify($arrayList)
    {
        $paramStr = '';
        $flag = 1;
        foreach ($arrayList as $value) {
            if ($flag) {
                $paramStr .= checkString_e($value);
                $flag = 0;
            } else {
                $paramStr .= '|' . checkString_e($value);
            }
        }
        return $paramStr;
    }
}

if (!function_exists('removeCheckSumParam')) {
    function removeCheckSumParam($arrayList)
    {
        if (isset($arrayList['CHECKSUMHASH'])) {
            unset($arrayList['CHECKSUMHASH']);
        }
        return $arrayList;
    }
}
