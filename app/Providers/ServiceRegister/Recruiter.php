<?php

namespace App\Providers\ServiceRegister;

use App\Services\Recruiter\Application\ApplicationService;
use App\Services\Recruiter\AuthService;
use App\Services\Recruiter\ChatService;
use App\Services\Recruiter\NotificationService;
use App\Services\Recruiter\PasswordResetService;
use App\Services\Recruiter\UserProfileService;
use App\Services\Recruiter\StoreService;
use App\Services\Recruiter\ProfileService;
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

        $app->scoped(UserProfileService::class, function ($app) {
            return new UserProfileService();
        });

        $app->scoped(StoreService::class, function ($app) {
            return new StoreService();
        });

        $app->scoped(ProfileService::class, function ($app) {
            return new ProfileService();
        });

        $app->scoped(ChatService::class, function ($app) {
            return new ChatService();
        });

        $app->scoped(ApplicationService::class, function ($app) {
            return new ApplicationService();
        });

        $app->scoped(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }
}
