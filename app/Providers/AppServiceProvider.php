<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use App\Providers\ServiceRegister\Admin;
use App\Providers\ServiceRegister\Common;
use App\Providers\ServiceRegister\Recruiter;
use App\Providers\ServiceRegister\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Admin::register($this->app);
        Common::register($this->app);
        User::register($this->app);
        Recruiter::register($this->app);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('greater_than_field', function ($attribute, $value, $parameters, $validator) {
            $min_field = $parameters[0];
            $data = $validator->getData();
            $min_value = $data[$min_field];
            return $value >= $min_value;
        });

        Validator::replacer('greater_than_field', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':field', $parameters[0], $message);
        });
    }
}
