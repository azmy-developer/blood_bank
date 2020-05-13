<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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



Route::group(['prefix'=>'v1','namespace'=>'Api'],function(){
    Route::post('register','AuthController@register');
    Route::post('login','AuthController@login');
    Route::post('restPassword','AuthController@restPassword');
    Route::post('newPassword','AuthController@newPassword');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('profile','AuthController@profile');
        Route::post('notificationsSettings','AuthController@notificationsSettings');
        Route::post('posts','MainController@posts');

    });
});

Route::group(['prefix'=>'v1','namespace'=>'Api'],function(){
    Route::get('governorates','MainController@governorates');
    Route::get('cities','MainController@cities');
    Route::get('settings','MainController@settings');
    Route::get('blood_types','MainController@blood_types');
    Route::post('contact','MainController@contact');
    Route::get('categories','MainController@categories');

});

