<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Format money
     *
     * @param $money
     * @return string
     */
    public static function formatMoney($money)
    {
        $money = floatval($money);
        $formatMoney = number_format($money, 2, '.', ',');
        $formatMoney = rtrim(rtrim($formatMoney, '0'), '.');

        return '￥' . $formatMoney;
    }

    /**
     * Generate Numeric OTP
     *
     * @param $length
     * @return string
     */
    public static function generateNumericOTP($length)
    {
        $result = null;
        for ($i = 1; $i <= $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}
