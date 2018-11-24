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

    Route::post('login','UserController@login');
    Route::post('register','UserController@register');

    Route::get('create_admin','UserController@create_dummy_admin');
    Route::post('change_password' , 'UserController@change_password');
    Route::group(['middleware' => ['auth:api']], function () {
        Route::resource('stories' , 'StoriesController');
        Route::resource('saved_links' , 'SavedLinksController');
        Route::resource('my_links' , 'MyLinksController');
        Route::post('update_user' , 'UserController@update_user');
        Route::post('add_update_bank_info' , 'UserBanksController@add_update_bank_info');


    });

});