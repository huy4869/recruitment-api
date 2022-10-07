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
Route::post('/upload-image', 'UploadImageController@upload')->name('uploadImage')->middleware('user');
Route::get('/zipcode', 'ZipcodeController@index')->name('getZipcode');

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/register', 'AuthController@register')->name('register');
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/me', 'AuthController@currentLoginUser')->name('currentLoginUser');
    Route::post('/me', 'AuthController@updateProfile')->name('updateProfile');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword')->middleware('user');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile'], function () {
    Route::get('/', 'ProfileController@getCompletionPercent')->name('getCompletionPercent')->middleware('user');
});

Route::group(['as' => 'applications.', 'prefix' => 'applications', 'middleware' => 'user'], function () {
    Route::get('/waiting-interview', 'ApplicationController@listWaitingInterview')->name('listWaitingInterview');
    Route::get('/applied', 'ApplicationController@listApplied')->name('listApplied');
    Route::post('/cancel', 'ApplicationController@cancelApplied')->name('cancelApplied');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile', 'middleware' => 'user'], function () {
    Route::get('/', 'UserController@detail')->name('detail');
    Route::post('/update', 'UserController@update')->name('update');
    Route::group(['as' => 'basic-info.', 'prefix' => 'basic-info'], function () {
    });
});

Route::group(['as' => 'contact.', 'prefix' => 'contact'], function () {
    Route::post('/create', 'ContactController@store')->name('store');
    Route::get('/admin-tel', 'ContactController@getAdminPhone')->name('getAdminPhone');
});

Route::group(['as' => 'job.', 'prefix' => 'job', 'middleware' => 'user'], function (){
    Route::delete('/delete-favorite/{id}', 'JobController@deleteFavoriteJob')->name('deleteFavoriteJob');
});

Route::group(['as' => 'work-history.', 'prefix' => 'work-history', 'middleware' => 'user'], function () {
    Route::get('/', 'WorkHistoryController@list')->name('list');
});

Route::group(['as' => 'feedback.', 'prefix' => 'feedback', 'middleware' => 'user'], function () {
    Route::post('/{jobPosting}', 'FeedbackController@store')->name('store');
});
