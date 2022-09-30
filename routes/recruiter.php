<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/register', 'AuthController@register')->name('register');
});
