<?php

namespace App\Helpers;

use App\Models\User;

class UrlHelper
{
    /**
     * User url
     *
     * @param string $path
     * @param $user
     * @return string
     */
    public static function userUrl(string $path = '', $user)
    {
        switch ($user->role_id) {
            case User::ROLE_USER:
                $userPath = config('app.user_url');
                break;
            case User::ROLE_RECRUITER:
                $userPath = config('app.rec_url');
                break;
            case User::ROLE_SUB_ADMIN || User::ROLE_ADMIN:
                $userPath = config('app.admin_url');
                break;
        }

        $basePath = rtrim($userPath, '/');
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
     * @param $user
     * @return string
     */
    public static function resetPasswordLink(string $token, $user)
    {
        $path = config('password_reset.path.reset_password') . '?token=' . UrlHelper::urlEncode($token);
        return UrlHelper::userUrl($path, $user);
    }

    /**
     * verify register Link
     *
     * @param $token
     * @param $user
     * @return string
     */
    public static function verifyRegisterLink($token, $user)
    {
        $path = config('password_reset.path.verify_register') . '?token=' . $token;

        return UrlHelper::userUrl($path, $user);
    }
}
