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
});

Route::group(['as' => 'users.', 'prefix' => 'users'], function () {
    Route::get('/', 'UserController@index')->name('index');
    Route::get('/{user}', 'UserController@detail')->name('detail');
    Route::post('/{user}', 'UserController@update')->name('update');
});
