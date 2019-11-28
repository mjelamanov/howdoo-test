<?php

use App\Http\Middleware\CheckDocumentNotEmpty;
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

Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function () {

    Route::post('login', 'LoginController@login')->name('api.login');

    Route::middleware('auth:api')->group(function () {
        Route::post('document', 'DocumentController@store')->name('document.store');
        Route::put('document/{document}', 'DocumentController@update')->name('document.update');

        Route::post('document/{document}/publish', 'DocumentController@publish')
            ->name('document.publish')
            ->middleware(CheckDocumentNotEmpty::class);
    });

    Route::get('document', 'DocumentController@index')->name('document.name');
    Route::get('document/{document}', 'DocumentController@show')->name('document.show');

});
