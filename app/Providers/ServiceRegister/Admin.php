<?php

namespace App\Providers\ServiceRegister;

use App\Services\Admin\AuthService;
use App\Services\Admin\LearningHistoryService;
use App\Services\Admin\LicensesQualificationService;
use App\Services\Admin\MasterDataService;
use App\Services\Admin\PasswordResetService;
use App\Services\Admin\Store\StoreService;
use App\Services\Admin\User\UserService;
use App\Services\Admin\User\UserTableService;
use App\Services\Admin\WorkHistoryService;
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

        $app->scoped(PasswordResetService::class, function ($app) {
            return new PasswordResetService();
        });

        $app->scoped(StoreService::class, function ($app) {
            return new StoreService();
        });

        $app->scoped(WorkHistoryService::class, function ($app) {
            return new WorkHistoryService();
        });

        $app->scoped(LearningHistoryService::class, function ($app) {
            return new LearningHistoryService();
        });

        $app->scoped(LicensesQualificationService::class, function ($app) {
            return new LicensesQualificationService();
        });
    }
}
