<?php

namespace App\Helpers;


class ValidationRules {

    public static function string($value, $required = false) {
        return ($required ? !empty($value) : true) & is_string($value);
    }

    public static function date($value, $required = false) {
        return ($required ? !empty($value): true) & (date('Y-m-d', strtotime($value)) == $value);
    }

    public static function integer($value, $required = false){
        return ($required ? !empty($value): true) & is_numeric($value);
    }
}