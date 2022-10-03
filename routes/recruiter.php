<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Recruiter Routes
|--------------------------------------------------------------------------
|
| Here is where you can register user api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "recruiter" middleware group. Enjoy building your user api!
|
*/

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::post('/register', 'AuthController@register')->name('register');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});
