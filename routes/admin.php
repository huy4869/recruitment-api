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
    Route::get('/all-owner', 'UserController@getAllOwner');
    Route::get('/roles', 'UserController@availableActionRoles');
    Route::get('/', 'UserController@list')->name('list');
    Route::delete('/delete/{id}', 'UserController@destroy')->name('destroy');
    Route::get('/list-user', 'UserController@listInfoUser');
    Route::get('/{id}', 'UserController@detail')->name('detail');
    Route::get('/detail-pr/{id}', 'UserController@detailPr');
    Route::post('/update-pr/{id}', 'UserController@updatePr');
    Route::get('/detail-motivation/{id}', 'UserController@detailMotivation');
    Route::post('/update-motivation/{id}', 'UserController@updateMotivation');
    Route::post('/update-user/{id}', 'UserController@updateUser');
    Route::get('/{id}/detail', 'UserController@detailUser');
    Route::post('/', 'UserController@store')->name('store');
    Route::post('/{id}', 'UserController@update')->name('update');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});

Route::group(['as' => 'stores.', 'prefix' => 'stores', 'middleware' => 'admin'], function () {
    Route::get('/', 'StoreController@list');
    Route::get('/all', 'StoreController@all')->name('all');
    Route::get('/{id}', 'StoreController@detail');
    Route::post('/', 'StoreController@store');
    Route::post('/update/{id}', 'StoreController@update');
    Route::delete('/{id}', 'StoreController@delete');
});

Route::group(['as' => 'jobs.', 'prefix' => 'jobs', 'middleware' => 'admin'], function () {
    Route::get('/', 'JobController@list')->name('list');
    Route::post('/create', 'JobController@create')->name('create');
    Route::get('/{id}', 'JobController@detail')->name('detail');
    Route::post('/update/{id}', 'JobController@update')->name('update');
    Route::post('/delete/{id}', 'JobController@delete')->name('delete');
});

Route::group(['as' => 'applications.', 'prefix' => 'applications', 'middleware' => 'admin'], function () {
    Route::get('/', 'ApplicationController@list')->name('list');
    Route::get('/{id}', 'ApplicationController@detail')->name('detail');
    Route::get('/user/{id}/profile', 'ApplicationController@profileUser');
    Route::post('/{id}', 'ApplicationController@update')->name('update');
});

Route::group(['as' => 'interview-schedule.', 'prefix' => 'interview-schedule', 'middleware' => 'admin'], function () {
    Route::get('/', 'InterviewScheduleController@getInterviewSchedule')->name('getInterviewSchedule');
    Route::post('/', 'InterviewScheduleController@updateInterviewSchedule')->name('updateInterviewSchedule');
    Route::post('/update-date', 'InterviewScheduleController@updateOrCreateInterviewScheduleDate')->name('updateOrCreateInterviewScheduleDate');
    Route::get('/application/{id}', 'InterviewScheduleController@getInterviewScheduleApplication')->name('getInterviewScheduleApplication');
    Route::post('/application/{id}', 'InterviewScheduleController@updateApplication')->name('updateApplication');
});

Route::group(['as' => 'work-histories', 'prefix' => 'work-histories', 'middleware' => 'admin'], function () {
    Route::get('/{id}', 'WorkHistoryController@detail')->name('detail');
    Route::post('/', 'WorkHistoryController@store');
    Route::post('/{id}', 'WorkHistoryController@update');
    Route::delete('/{id}', 'WorkHistoryController@delete');
});

Route::group(['as' => 'learning-histories', 'prefix' => 'learning-histories', 'middleware' => 'admin'], function () {
    Route::get('/{id}', 'LearningHistoryController@detail');
    Route::post('/', 'LearningHistoryController@store');
    Route::post('/{id}', 'LearningHistoryController@update');
    Route::delete('/{id}', 'LearningHistoryController@delete');
});

Route::group(['as' => 'licenses-qualifications', 'prefix' => 'licenses-qualifications', 'middleware' => 'admin'], function () {
    Route::get('/{id}', 'LicensesQualificationController@detail');
    Route::delete('/{id}', 'LicensesQualificationController@delete');
    Route::post('/', 'LicensesQualificationController@store');
    Route::post('/{id}', 'LicensesQualificationController@update');
});

Route::group(['as' => 'contacts.', 'prefix' => 'contacts', 'middleware' => 'admin'], function () {
    Route::get('/', 'ContactsController@list')->name('list');
    Route::get('/{id}', 'ContactsController@detail')->name('detail');
});

Route::group(['as' => 'feedback-jobs.', 'prefix' => 'feedback-jobs', 'middleware' => 'admin'], function () {
    Route::get('/', 'FeedbackJobsController@list')->name('list');
    Route::get('/{id}', 'FeedbackJobsController@detail')->name('detail');
});
