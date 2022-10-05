<?php

namespace App\Helpers;

class UserHelper
{
    public static function getPercentage($data, $percentage)
    {
        foreach ($data as $value) {
            if (is_null($value)) {
                $percentage = config('percentage.default');
                break;
            }
        }

        return $percentage;
    }
}
