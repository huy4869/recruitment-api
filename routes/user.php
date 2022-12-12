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

Route::group(['as' => 'favoriteJob.', 'prefix' => 'favorite-job', 'middleware' => 'user'], function () {
    Route::get('/', 'JobController@getFavoriteJob')->name('favoriteJob');
    Route::delete('/delete/{id}', 'JobController@deleteFavoriteJob')->name('deleteFavoriteJob');
    Route::post('/', 'JobController@storeFavorite');
});

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::post('/register', 'AuthController@register')->name('register');
    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/me', 'AuthController@currentLoginUser')->name('currentLoginUser');
    Route::post('/me', 'AuthController@updateProfile')->name('updateProfile');
    Route::post('/change-password', 'AuthController@changePassword')->name('changePassword')->middleware('user');
    Route::post('/verify-register', 'AuthController@verifyRegister')->name('verifyRegister');
});

Route::group(['as' => 'forgot-password.', 'prefix' => 'forgot-password'], function () {
    Route::post('/', 'PasswordResetController@sendMail')->name('sendMail');
    Route::post('/check-token', 'PasswordResetController@checkToken')->name('check-token');
    Route::post('/reset-password', 'PasswordResetController@resetPassword')->name('reset.password');
});

Route::group(['as' => 'applications.', 'prefix' => 'applications', 'middleware' => 'user'], function () {
    Route::get('/', 'ApplicationController@list')->name('list');
    Route::post('/', 'ApplicationController@store')->name('store');
    Route::get('/waiting-interview', 'ApplicationController@listWaitingInterview')->name('listWaitingInterview');
    Route::get('/applied', 'ApplicationController@listApplied')->name('listApplied');
    Route::post('/cancel/{id}', 'ApplicationController@cancelApplied')->name('cancelApplied');
    Route::post('/cancel', 'ApplicationController@cancelApplied')->name('cancelApplied');
    Route::get('/{id}', 'ApplicationController@detail')->name('detail');
    Route::post('/{id}', 'ApplicationController@update')->name('update');
});

Route::group(['as' => 'profile.', 'prefix' => 'profile', 'middleware' => 'user'], function () {
    Route::get('/', 'UserController@detail')->name('detail');
    Route::post('/update', 'UserController@update')->name('update');
    Route::group(['as' => 'basic-info.', 'prefix' => 'basic-info'], function () {
    });
    Route::get('/pr', 'UserController@detailPr')->name('list_pr');
    Route::post('/pr', 'UserController@updatePr')->name('update_pr');
    Route::get('/motivation', 'UserController@detailMotivation')->name('detail_motivation');
    Route::post('/motivation', 'UserController@updateMotivation')->name('update_motivation');
    Route::get('/percentage', 'ProfileController@getCompletionPercent')->name('getCompletionPercent');
});

Route::group(['as' => 'contact.', 'prefix' => 'contact'], function () {
    Route::post('/create', 'ContactController@store')->name('store');
    Route::get('/admin-tel', 'ContactController@getAdminPhone')->name('getAdminPhone');
});

Route::group(['as' => 'job.', 'prefix' => 'job'], function () {
    Route::get('/', 'JobController@list')->name('list');
    Route::get('/news', 'JobController@getListNewJobPostings')->name('getListNewJobPostings');
    Route::get('/most-views', 'JobController@getListMostViewJobPostings')->name('getListMostViewJobPostings');
    Route::get('/most-favorites', 'JobController@getListMostFavoriteJobPostings')->name('getListMostFavoriteJobPostings');
    Route::get('/recommends', 'JobController@getListRecommends')->name('getListRecommends');
    Route::get('/recent', 'JobController@recentJobs')->name('recentJobs');
    Route::get('/suggest/{id}', 'JobController@suggestJobs')->name('suggestJobs');
    Route::get('/total', 'JobController@totalJobs')->name('totalJobs');
    Route::get('/{id}', 'JobController@detail')->name('detail');
    Route::get('/{job}/application', 'JobController@detailJobUserApplication')->name('detail')->middleware('user');
});

Route::group(['as' => 'work-history.', 'prefix' => 'work-history', 'middleware' => 'user'], function () {
    Route::get('/', 'WorkHistoryController@list')->name('list');
    Route::post('/', 'WorkHistoryController@store')->name('store');
    Route::get('/{id}', 'WorkHistoryController@detail')->name('detail');
    Route::post('/{id}', 'WorkHistoryController@update')->name('update');
    Route::post('/{id}/delete', 'WorkHistoryController@delete')->name('delete');
});

Route::group(['as' => 'feedback.', 'prefix' => 'feedback', 'middleware' => 'user'], function () {
    Route::post('/{job_id}', 'FeedbackController@store')->name('store');
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
    Route::get('/{id}', 'LicensesQualificationController@detail')->name('detail');
    Route::post('/{id}', 'LicensesQualificationController@update')->name('update');
    Route::post('/{id}/delete', 'LicensesQualificationController@delete')->name('delete');
});

Route::group(['as' => 'learning-history.', 'prefix' => 'learning-history', 'middleware' => 'user'], function () {
    Route::get('/', 'LearningHistoryController@list')->name('list');
    Route::post('/', 'LearningHistoryController@store')->name('store');
    Route::get('/{id}', 'LearningHistoryController@detail')->name('detail');
    Route::post('/{id}', 'LearningHistoryController@update')->name('update');
    Route::post('/{id}/delete', 'LearningHistoryController@delete')->name('delete');
});

Route::group(['as' => 'notifications.', 'prefix' => 'notifications', 'middleware' => 'user'], function () {
    Route::get('/', 'NotificationController@list')->name('list');
    Route::get('/count', 'NotificationController@count');
    Route::post('/read/{id}', 'NotificationController@updateBeReadNotification')->name('updateBeReadNotification');
});

Route::group(['as' => 'search-jobs.', 'prefix' => 'search-jobs', 'middleware' => 'user'], function () {
    Route::get('/', 'SearchJobController@list')->name('list');
    Route::delete('/delete/{id}', 'SearchJobController@destroy')->name('destroy');
});

Route::group(['as' => 'location.', 'prefix' => 'location'], function () {
    Route::get('/most-apply', 'LocationController@getAccordingToMostApply')->name('getMostApply');
    Route::get('/amount-job-in-province', 'LocationController@amountJobInProvince')->name('amountJobInProvince');
});

Route::group(['as' => 'job-type.', 'prefix' => 'job-type'], function () {
    Route::get('/amount-job-in-job-type', 'JobTypeController@amountJobInJobTypes')->name('amountJobInJobTypes');
});
