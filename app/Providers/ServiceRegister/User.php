<?php

namespace App\Providers\ServiceRegister;

use App\Services\User\ApplicationService;
use App\Services\User\AuthService;
use App\Services\User\Job\JobService;
use App\Services\User\MasterDataService;
use App\Services\User\PasswordResetService;
use App\Services\User\ProfileService;
use App\Services\User\WorkHistoryService;
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

        $app->scoped(PasswordResetService::class, function ($app) {
            return new PasswordResetService();
        });

        $app->scoped(ProfileService::class, function ($app) {
            return new ProfileService();
        });

        $app->scoped(ApplicationService::class, function ($app) {
            return new ApplicationService();
        });

        $app->scoped(JobService::class, function ($app) {
            return new JobService();
        });

        $app->scoped(WorkHistoryService::class, function ($app) {
            return new WorkHistoryService();
        });
    }
}
