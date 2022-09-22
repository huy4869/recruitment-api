<?php

namespace App\Providers\ServiceRegister;

use App\Services\Admin\AuthService;
use App\Services\Admin\MasterDataService;
use App\Services\Admin\User\UserService;
use App\Services\Admin\User\UserTableService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class Admin
{
    /**
     * Register Admin Service
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

        $app->scoped(UserTableService::class, function ($app) {
            return new UserTableService();
        });

        $app->scoped(UserService::class, function ($app) {
            return new UserService();
        });
    }
}
