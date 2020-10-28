<?php

use Illuminate\Support\Facades\Route;

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
    return view('home');
});


Route::get('/attach', [AjaxController::class, 'attach']);

Route::get('/home', [AjaxController::class, 'index']);
Route::get('/login', [AjaxController::class, 'login']);
Route::post('/login', [AjaxController::class, 'loginPost']);
Route::get('/logout', [AjaxController::class, 'logout']);
