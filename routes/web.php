<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::group(['namespace' => 'Dashboard\Auth', 'middleware' => 'set_locale'], function () {

    // admin login routes
    Route::get('/', 'AdminAuthController@showLoginForm');

    Route::get('admin/login', 'AdminAuthController@showLoginForm')->name('admin.login-form');
    Route::post('admin/login', 'AdminAuthController@login')->middleware('throttle:10,1')->name('admin.login');
    Route::post('admin/logout', 'AdminAuthController@logout')->name('admin.logout');
});


