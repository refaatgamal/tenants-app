<?php

use App\Http\Controllers\AuthController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api' , 'prefix' => 'auth'],function(){
    Route::controller(AuthController::class)->group(function(){
        Route::post('register','register');
        Route::post('login','login');
        Route::post('logout','logout');
    });
});

Route::group(['middleware' => 'api'],function(){
    Route::resource('products', App\Http\Controllers\ProductController::class);
});

