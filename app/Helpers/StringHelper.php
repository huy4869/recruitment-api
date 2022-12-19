<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringHelper
{
    /**
     * Slug
     *
     * @param string $str
     * @return string
     */
    public static function slug(string $str)
    {
        return Str::slug($str);
    }

    /**
     * Trim string space
     *
     * @param string $str
     * @return string
     */
    public static function trimSpace(string $str)
    {
        if (!$str || !is_string($str)) {
            return '';
        }

        return trim(preg_replace('!\s+!', ' ', $str));
    }

    /**
     * Unique Code
     *
     * @param $limit
     * @return false|string
     */
    public static function uniqueCode($limit = 6)
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    public static function escapeLikeSearch($string)
    {
        $search = array('\\', '_', '%');
        $replace   = array('\\\\', '\_', '\%');

        return str_replace($search, $replace, $string);
    }
}
