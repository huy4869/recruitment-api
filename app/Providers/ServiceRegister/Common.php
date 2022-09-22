<?php

namespace App\Providers\ServiceRegister;

use App\Services\Common\FileService;
use App\Services\Common\ZipcodeService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class Common
{
    /**
     * Register Common Service
     *
     * @param Application|Container $app
     * @return void
     */
    public static function register($app)
    {
        $app->scoped(FileService::class, function ($app) {
            return new FileService();
        });

        $app->scoped(ZipcodeService::class, function ($app) {
            return new ZipcodeService();
        });
    }
}
