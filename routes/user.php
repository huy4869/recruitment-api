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
    Route::get('/', 'ApplicationController@list')->name('list');
    Route::get('/waiting-interview', 'ApplicationController@listWaitingInterview')->name('listWaitingInterview');
    Route::get('/applied', 'ApplicationController@listApplied')->name('listApplied');
    Route::post('/cancel', 'ApplicationController@cancelApplied')->name('cancelApplied');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile', 'middleware' => 'user'], function () {
    Route::get('/', 'UserController@detail')->name('detail');
    Route::post('/update', 'UserController@update')->name('update');
    Route::group(['as' => 'basic-info.', 'prefix' => 'basic-info'], function () {
    });
    Route::get('/pr', 'UserController@detailPr')->name('list_pr');
    Route::post('/pr', 'UserController@updatePr')->name('update_pr');
});

Route::group(['as' => 'contact.', 'prefix' => 'contact'], function () {
    Route::post('/create', 'ContactController@store')->name('store');
    Route::get('/admin-tel', 'ContactController@getAdminPhone')->name('getAdminPhone');
});

Route::group(['as' => 'job.', 'prefix' => 'job'], function () {
    Route::delete('/delete-favorite/{id}', 'JobController@deleteFavoriteJob')->name('deleteFavoriteJob')->middleware('user');
    Route::get('/favorite-job', 'JobController@getFavoriteJob')->name('favoriteJob')->middleware('user');
    Route::get('/news', 'JobController@getListNewJobPostings')->name('getListNewJobPostings');
    Route::get('/most-views', 'JobController@getListMostViewJobPostings')->name('getListMostViewJobPostings');
    Route::get('/most-applies', 'JobController@getListMostApplyJobPostings')->name('getListMostApplyJobPostings');
    Route::get('/recent', 'JobController@recentJobs')->name('recentJobs');
    Route::get('/suggest/{id}', 'JobController@suggestJobs')->name('suggestJobs');
    Route::get('/{id}', 'JobController@detail')->name('detail');
});

Route::group(['as' => 'work-history.', 'prefix' => 'work-history', 'middleware' => 'user'], function () {
    Route::get('/', 'WorkHistoryController@list')->name('list');
    Route::post('/', 'WorkHistoryController@store')->name('store');
    Route::get('/{workHistory}', 'WorkHistoryController@detail')->name('detail');
    Route::post('/{workHistory}', 'WorkHistoryController@update')->name('update');
    Route::post('/{workHistory}/delete', 'WorkHistoryController@delete')->name('delete');
});

Route::group(['as' => 'feedback.', 'prefix' => 'feedback', 'middleware' => 'user'], function () {
    Route::post('/{jobPosting}', 'FeedbackController@store')->name('store');
});

Route::group(['as' => 'chat.', 'prefix' => 'chat', 'middleware' => 'user'], function () {
    Route::get('/list', 'ChatController@list')->name('list');
    Route::get('/list-detail/{store_id}', 'ChatController@detail')->name('detail');
    Route::post('/create', 'ChatController@store')->name('store');
    Route::get('/unread-count', 'ChatController@unreadCount')->name('unreadCount');
});

Route::group(['as' => 'desired-condition.', 'prefix' => 'desired-condition', 'middleware' => 'user'], function () {
    Route::get('/', 'DesiredConditionController@detail')->name('detail');
    Route::post('/', 'DesiredConditionController@storeOrUpdate')->name('store_or_update');
});

Route::group(['as' => 'licenses-qualifications.', 'prefix' => 'licenses-qualifications', 'middleware' => 'user'], function () {
    Route::get('/', 'LicensesQualificationController@list')->name('list');
    Route::post('/', 'LicensesQualificationController@store')->name('store');
    Route::get('/{licensesQualification}', 'LicensesQualificationController@detail')->name('detail');
    Route::post('/{licensesQualification}', 'LicensesQualificationController@update')->name('update');
    Route::post('/{licensesQualification}/delete', 'LicensesQualificationController@delete')->name('delete');
});

Route::group(['as' => 'learning-history.', 'prefix' => 'learning-history', 'middleware' => 'user'], function () {
    Route::get('/', 'LearningHistoryController@list')->name('list');
    Route::post('/', 'LearningHistoryController@store')->name('store');
    Route::get('/{learningHistory}', 'LearningHistoryController@detail')->name('detail');
    Route::post('/{learningHistory}', 'LearningHistoryController@update')->name('update');
    Route::post('/{learningHistory}/delete', 'LearningHistoryController@delete')->name('delete');
});
