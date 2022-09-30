<?php

namespace App\Providers\ServiceRegister;

use App\Services\Recruiter\AuthService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class Recruiter
{
    /**
     * @param Container|Application $app
     * @return void
     */
    public static function register($app)
    {
        $app->scoped(AuthService::class, function ($app){
            return new AuthService();
        });
    }
}
