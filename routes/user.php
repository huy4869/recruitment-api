<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Here is where you can register user api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "user" middleware group. Enjoy building your user api!
|
*/

Route::get('/master-data', 'MasterDataController@show')->name('masterData');
Route::post('/upload-image', 'UploadImageController@upload')->name('uploadImage');
Route::get('/zipcode', 'ZipcodeController@index')->name('getZipcode');

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/register', 'AuthController@register')->name('register');
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/me', 'AuthController@currentLoginUser')->name('currentLoginUser');
    Route::post('/me', 'AuthController@updateProfile')->name('updateProfile');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword');
});
