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

use App\Events\MessageEvent;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signUp', 'AuthController@signUp');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:api', 'check.admin'],
], function () {
    Route::get('authorizeAdmin', 'Admin\AdminController@authorizeAdmin');
    Route::get('getAdminMenuCategories', 'Admin\AdminController@getAdminMenuCategories');
    Route::get('getUsersList', 'Admin\UserController@index');
});

Route::group([
    'prefix' => 'user',
    'middleware' => 'auth:api'
], function () {
    Route::post('getUserData', 'User\UserController@getUserData');
    Route::post('saveProfile', 'User\UserController@saveProfile');
});

Route::group([
    'prefix' => 'message',
    'middleware' => 'auth:api'
], function() {
   Route::post('sendMessage', 'User\MessageController@sendMessage');
   Route::get('fetchConversations', 'User\MessageController@fetchConversations');
   Route::post('fetchMessages', 'User\MessageController@fetchMessages');
   Route::post('markAsRead', 'User\MessageController@markAsRead');
});

Route::get('event', function () {
    event(new MessageEvent('hello world'));
});
