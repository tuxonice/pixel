<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\HomeController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index']);
Route::group(['middleware' => 'api'], function () {
    Route::prefix('api')->group(function () {
        Route::get('v1/{category?}', [IndexController::class, 'index']);
    });
    Route::get('json/{category?}', [IndexController::class, 'json']);
});
