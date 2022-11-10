<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "admin" middleware group. Enjoy building your admin api!
|
*/

Route::get('/master-data', 'MasterDataController@show')->name('masterData');
Route::post('/upload-image', 'UploadImageController@upload')->name('uploadImage');
Route::get('/zipcode', 'ZipcodeController@index')->name('getZipcode');

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/me', 'AuthController@currentLoginUser')->name('currentLoginUser');
    Route::post('/me', 'AuthController@updateProfile')->name('updateProfile');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword');
    Route::post('/register', 'AuthController@register')->name('register');
});

Route::group(['as' => 'users.', 'prefix' => 'users', 'middleware' => 'admin'], function () {
    Route::get('/', 'UserController@list')->name('list');
    Route::delete('/delete/{user}', 'UserController@destroy')->name('destroy');
    Route::post('/update-user/{id}', 'UserController@updateUser');
    Route::get('/{id}/detail', 'UserController@detailUser');
    Route::get('/{user}', 'UserController@detail')->name('detail');
    Route::post('/', 'UserController@store')->name('store');
    Route::post('/{user}', 'UserController@update')->name('update');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});

Route::group(['as' => 'stores.', 'prefix' => 'stores', 'middleware' => 'admin'], function () {
    Route::get('/', 'StoreController@list');
    Route::get('/{id}', 'StoreController@detail');
    Route::post('/', 'StoreController@store');
    Route::post('/update/{id}', 'StoreController@update');
});

Route::group(['as' => 'jobs.', 'prefix' => 'jobs', 'middleware' => 'admin'], function () {
    Route::get('/', 'JobController@list')->name('list');
    Route::post('/', 'JobController@store')->name('store');
    Route::get('/{id}', 'JobController@detail')->name('detail');
    Route::post('/{id}', 'JobController@update')->name('update');
});

Route::group(['as' => 'applications.', 'prefix' => 'applications', 'middleware' => 'admin'], function () {
    Route::get('/', 'ApplicationController@list')->name('list');
    Route::get('/{id}', 'ApplicationController@detail')->name('detail');
    Route::post('/{id}', 'ApplicationController@update')->name('update');
});

Route::group(['as' => 'interview-schedule.', 'prefix' => 'interview-schedule', 'middleware' => 'admin'], function () {
    Route::get('/', 'InterviewScheduleController@getInterviewSchedule')->name('getInterviewSchedule');
    Route::post('/application/{id}', 'InterviewScheduleController@updateApplication')->name('updateApplication');
    Route::post('/{user_id}', 'InterviewScheduleController@updateInterviewSchedule')->name('updateInterviewSchedule');
});
