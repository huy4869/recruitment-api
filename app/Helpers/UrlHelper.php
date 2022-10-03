<?php

namespace App\Helpers;

class UrlHelper
{
    /**
     * User url
     *
     * @param string $path
     * @return string
     */
    public static function userUrl(string $path = '')
    {
        $basePath = rtrim(config('app.user_url'), '/');
        if (!$path) {
            return $basePath;
        }

        return $basePath . '/' . ltrim($path, '/');
    }

    /**
     * URL encode
     *
     * @param string $str
     * @return string
     */
    public static function urlEncode(string $str)
    {
        return rawurlencode($str);
    }

    /**
     * Reset Password Link
     *
     * @param string $token
     * @return string
     */
    public static function resetPasswordLink(string $token)
    {
        $path = config('password_reset.path.reset_password') . '?token=' . UrlHelper::urlEncode($token);
        return UrlHelper::userUrl($path);
    }
}
