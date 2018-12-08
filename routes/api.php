<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors']], function () {

   
    Route::post('register','UserController@register');
    Route::post('login','UserController@login');
    Route::get('create_admin','UserController@create_dummy_admin');
    Route::post('change_password' , 'UserController@change_password');
    Route::post('add_update_mylinks' , 'MyLinksController@add_update_mylinks');
    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::get('visiting_story/{user_id}/{stories_id}','StoriesController@visiting_story');
    Route::get('visiting_story_secure/{user_id}/{stories_id}','StoriesController@visiting_story_secure');
    Route::get('visiting_story_callback/{key}','StoriesController@visiting_story_callback');



    Route::group(['middleware' => ['last_online']], function () { 

        Route::group(['middleware' => ['auth:api']], function () {
            Route::resource('stories' , 'StoriesController');
            Route::resource('saved_links' , 'SavedLinksController');
            Route::resource('my_links' , 'MyLinksController');
            Route::resource('banks' , 'BanksController');
            Route::resource('payments' , 'PaymentsController');
            Route::resource('facebook_pages' , 'FacebookPagesController');
            Route::resource('notifications' , 'NotificationsController');
            Route::post('update_notification_status' , 'NotificationsController@update_notification_status');
            Route::post('update_user' , 'UserController@update_user');
            Route::post('add_update_bank_info' , 'UserBanksController@add_update_bank_info');
            Route::get('dashboard','UserController@dashboard');
            Route::post('dashboard_date','UserController@dashboard_date');

            Route::get('visits','StoriesController@get_visits');


            //admin
            Route::get('get_all_payments','AdminController@get_all_payments');
            Route::get('get_all_users','AdminController@get_all_users');

            Route::post('change_user_status' , 'AdminController@change_user_status');
            Route::post('change_payment_status' , 'AdminController@change_payment_status');
            Route::post('add_payment' , 'AdminController@add_payment');

            Route::get('dummy_payments','PaymentsController@dummy_payments');
            Route::any('settings','AdminController@settings');
        });
    });

});