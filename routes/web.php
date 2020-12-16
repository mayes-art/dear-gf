<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LobbyController;

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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('info', function () {
    phpinfo();
});

Route::get('/', [LobbyController::class, 'lineGet']);
Route::post('/', [LobbyController::class, 'linePost']);

Route::post('test', [LobbyController::class, 'test']);

