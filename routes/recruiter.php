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
Route::post('/upload-image', 'UploadImageController@upload')->name('uploadImage')->middleware('recruiter');

Route::get('/master-data', 'MasterDataController@show')->name('masterData')->middleware('recruiter');

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::post('/register', 'AuthController@register')->name('register');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword')->middleware('recruiter');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});

Route::group(['as' => 'users.', 'prefix' => 'users', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'UserController@list')->name('list');
    Route::get('/new', 'UserController@newUsers')->name('newUsers');
    Route::get('/suggest', 'UserController@suggestUsers')->name('suggestUsers');
    Route::get('/detail', 'UserProfileController@detail')->name('detail');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'ProfileController@getInformation')->name('getInformation');
    Route::post('/', 'ProfileController@update')->name('update');
});

Route::group(['as' => 'jobs.', 'prefix' => 'jobs', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'JobController@list')->name('list');
    Route::get('/{id}', 'JobController@detail')->name('detail');
    Route::post('/delete/{id}', 'JobController@destroy')->name('destroy');
    Route::post('/update/{id}', 'JobController@update')->name('update');
    Route::post('/create', 'JobController@create')->name('create');
    Route::get('/all', 'JobController@listJobNameByOwner')->name('listJobNameByOwner');
});

Route::group(['as' => 'store.', 'prefix' => 'store', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'StoreController@list')->name('list');
    Route::get('/all', 'StoreController@listStoreNameByOwner')->name('listStoreNameByOwner');
});
