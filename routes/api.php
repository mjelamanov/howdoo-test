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

    Route::resource('document', 'DocumentController')->except(['create', 'edit', 'destroy']);
    Route::post('document/{document}/publish', 'DocumentController@publish')
        ->name('document.publish')
        ->middleware(CheckDocumentNotEmpty::class);

});
