<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors', 'json.response']], function () {

    Route::post('login', 'Auth\AuthController@loginByEmail');
    Route::post('login-otp/{customer:phone}', 'Auth\AuthController@loginOTP');
    Route::post('register', 'Auth\AuthController@register');

    Route::post('send-otp/{phone}', 'Auth\ForgetPasswordController@sendOtp');
    Route::post('check-otp/{customer:phone}', 'Auth\ForgetPasswordController@checkOTP');
    Route::post('change-password/{customer:phone}', 'Auth\ForgetPasswordController@changePassword');
    Route::get('resend-otp/{customer:phone}', 'Auth\ForgetPasswordController@reSendOtp');

    Route::get('about_us', 'HomeController@getAboutUs');
    Route::get('privacy_policy', 'HomeController@getprivacypolicy');

    Route::post('news-letter', 'HomeController@newsLetter');
    Route::get('services', 'HomeController@getservices');
    Route::get('whyus', 'HomeController@getwhyus');
    Route::get('questions', 'HomeController@getQuestions');
    Route::get('how-make-order', 'HomeController@getMakeOrder');
    Route::get('cities', 'HomeController@getcities');
    Route::get('rates', 'HomeController@getrates');

 // Step 1 - 4: Progressive validation (you may store values in session/temp)
Route::post('/step/{step}', [OrderController::class, 'handleStep']);
Route::post('/orders/{order}/resend-otp', [OrderController::class, 'resendOtp']);

// Step 4: Create Order with OTP after validating customer info
Route::post('/create', [OrderController::class, 'preCreateOrder']);

// Step 5: Confirm Order with OTP
    Route::post('/confirm', [OrderController::class, 'confirmOrderOtp']);

    Route::get('general', 'GeneralInvokableController');

    Route::get('sliders', 'HomeController@getSliders');
    Route::get('categories', 'HomeController@getCategories');

    Route::post('Ask_us', 'ContactUsController@store');
    Route::get('Ask_us', 'ContactUsController@data');


});
