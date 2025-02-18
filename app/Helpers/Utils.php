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
