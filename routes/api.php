<?php


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

Route::middleware(['auth:api'])->group(function () {
    Route::get('videos_by_course/{id}', 'CourseController@getVideosByCourse');


});

Route::group(['middleware' => ['cors', 'json.response']], function () {

    Route::post('login', 'Auth\AuthController@login');
    Route::post('login-otp/{customer:phone}', 'Auth\AuthController@loginOTP');
    Route::post('register', 'Auth\AuthController@register');

    Route::post('send-otp/{phone}', 'Auth\ForgetPasswordController@sendOtp');
    Route::post('check-otp/{customer:phone}', 'Auth\ForgetPasswordController@checkOTP');
    Route::post('change-password/{customer:phone}', 'Auth\ForgetPasswordController@changePassword');
    Route::get('resend-otp/{customer:phone}', 'Auth\ForgetPasswordController@reSendOtp');

    Route::get('about_us', 'HomeController@getAboutUs');
    Route::get('privacy_policy', 'HomeController@getprivacypolicy');
    Route::get('governments', 'HomeController@getgovernments');
    Route::get('home', 'HomeController@getHome');
    Route::get('categories', 'HomeController@getCategory');
    Route::get('footer', 'HomeController@getfooter');
    Route::get('courses_by_category', 'CourseController@getCoursesByCategory');
    Route::get('courses_by_id/{id}', 'CourseController@getCoursesById');
    Route::post('news-letter', 'HomeController@newsLetter');


});
