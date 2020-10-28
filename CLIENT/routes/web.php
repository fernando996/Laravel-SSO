<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home', [ClientController::class, 'index']);
Route::get('/login', [ClientController::class, 'login']);
Route::post('/login', [ClientController::class, 'loginPost']);
Route::get('/logout', [ClientController::class, 'logout']);