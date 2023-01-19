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
Route::get('/zipcode', 'ZipcodeController@index')->name('getZipcode');
Route::get('/master-data', 'MasterDataController@show')->name('masterData')->middleware('recruiter');

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/me', 'AuthController@me')->name('me')->middleware('recruiter');
    Route::post('/register', 'AuthController@register')->name('register');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword')->middleware('recruiter');
    Route::post('/verify-register', 'AuthController@verifyRegister')->name('verifyRegister');
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
    Route::get('/detail/{id}', 'UserProfileController@detail')->name('detail');
    Route::get('/favorites', 'UserController@listFavorite')->name('listFavorite');
    Route::post('/favorite', 'UserController@addOrRemoveFavoriteUser')->name('addOrRemoveFavoriteUser');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'ProfileController@getInformation')->name('getInformation');
    Route::post('/', 'ProfileController@update')->name('update');
});

Route::group(['as' => 'jobs.', 'prefix' => 'jobs', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'JobController@list')->name('list');
    Route::get('/all', 'JobController@listJobNameByOwner')->name('listJobNameByOwner');
    Route::get('/{id}', 'JobController@detail')->name('detail');
    Route::post('/delete/{id}', 'JobController@destroy')->name('destroy');
    Route::post('/update/{id}', 'JobController@update')->name('update');
    Route::post('/create', 'JobController@create')->name('create');
});

Route::group(['as' => 'stores.', 'prefix' => 'stores', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'StoreController@list')->name('list');
    Route::post('/', 'StoreController@store')->name('store');
    Route::post('/update/{id}', 'StoreController@update')->name('update');
    Route::delete('/{id}', 'StoreController@delete')->name('delete');
    Route::get('/all', 'StoreController@listStoreNameByOwner')->name('listStoreNameByOwner');
    Route::get('/{id}', 'StoreController@detail')->name('detail');
});

Route::group(['as' => 'applications.', 'prefix' => 'applications', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'ApplicationController@list')->name('list');
    Route::get('/user-profile/{id}', 'ApplicationController@profileUser');
    Route::get('/{id}', 'ApplicationController@detail')->name('detail');
    Route::post('/{id}', 'ApplicationController@update')->name('update');
});

Route::group(['as' => 'contacts.', 'prefix' => 'contacts', 'middleware' => 'recruiter'], function () {
    Route::get('/all', 'StoreController@listStoreNameByOwner')->name('listStoreNameByOwner');
    Route::post('/', 'ContactController@store')->name('store');
    Route::get('/admin-tel', 'ContactController@getAdminPhone')->name('getAdminPhone');
});

Route::group(['as' => 'chats.', 'prefix' => 'chats', 'middleware' => 'recruiter'], function () {
    Route::get('/list/{store_id?}', 'ChatController@getChatListOfStore');
    Route::post('/', 'ChatController@store');
    Route::get('/stores', 'ChatController@getListStore');
    Route::get('/count', 'ChatController@count');
    Route::get('/detail/{store_id}', 'ChatController@getDetailChat');
});

Route::group(['as' => 'interview-schedule.', 'prefix' => 'interview-schedule', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'InterviewScheduleController@getInterviewSchedule')->name('getInterviewSchedule');
    Route::post('/', 'InterviewScheduleController@updateOrCreateInterviewSchedule')->name('updateOrCreateInterviewSchedule');
    Route::post('/update-date', 'InterviewScheduleController@updateOrCreateInterviewScheduleDate')->name('updateOrCreateInterviewScheduleDate');
});

Route::group(['as' => 'notifications.', 'prefix' => 'notifications', 'middleware' => 'recruiter'], function () {
    Route::get('/', 'NotificationController@getNotify');
    Route::get('/count', 'NotificationController@count');
    Route::post('/update-read/{id}', 'NotificationController@updateBeReadNotify');
    Route::get('/announcement/matching', 'NotificationController@matchingAnnouncement');
    Route::post('/announcement/update-matching', 'NotificationController@updateMatching');
});
