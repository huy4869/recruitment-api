<?php

namespace App\Providers\ServiceRegister;

use App\Services\User\AuthService;
use App\Services\User\MasterDataService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class User
{
    /**
     * Register User Service
     *
     * @param Application|Container $app
     * @return void
     */
    public static function register($app)
    {
        $app->scoped(AuthService::class, function ($app) {
            return new AuthService();
        });

        $app->scoped(MasterDataService::class, function ($app) {
            return new MasterDataService();
        });
    }
}
