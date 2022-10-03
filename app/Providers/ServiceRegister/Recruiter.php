<?php

namespace App\Providers\ServiceRegister;

use App\Services\Recruiter\AuthService;
use App\Services\Recruiter\PasswordResetService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class Recruiter
{
    /**
     * @param Application|Container $app
     * @return void
     */
    public static function register($app)
    {
        $app->scoped(AuthService::class, function ($app) {
            return new AuthService();
        });

        $app->scoped(PasswordResetService::class, function ($app) {
            return new PasswordResetService();
        });
    }
}
