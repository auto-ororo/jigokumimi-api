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
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('create', 'AuthController@create');
    Route::put('changePassword', 'AuthController@changePassword');
    Route::delete('delete', 'AuthController@destroy');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
});

Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    Route::get('tracks', 'TracksAroundController@index');
    Route::post('tracks', 'TracksAroundController@store');
    Route::get('tracks/history', 'TracksAroundController@history');
    Route::delete('tracks/history', 'TracksAroundController@deleteHistory');
    Route::get('artists', 'ArtistsAroundController@index');
    Route::post('artists', 'ArtistsAroundController@store');
    Route::get('artists/history', 'ArtistsAroundController@history');
    Route::delete('artists/history', 'ArtistsAroundController@deleteHistory');
});
