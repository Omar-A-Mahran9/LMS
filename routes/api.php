<?php

use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\StudentQuizController;
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
    Route::get('classes_by_courses_id/{id}', 'CourseController@getClassesByCoursesId');

    Route::get('videos_by_course/{id}', 'CourseController@getVideosByCourse');
    Route::get('class/{id}', 'CourseController@getClassById');
    Route::get('quiz_by_class_id/{id}', 'CourseController@getQuizClassById');
    Route::get('quiz/{id}', 'CourseController@getQuizById');
    Route::get('quizzes/{quizId}/start', [StudentQuizController::class, 'startQuiz']);
    Route::post('student-quizzes/{quizAttemptId}/submit', [StudentQuizController::class, 'submitQuiz']);
    Route::get('student-quizzes/{studentQuizId}/results', [StudentQuizController::class, 'results']);
    Route::get('homework_by_course/{id}', 'CourseController@getVideosByCourse');
    Route::post('quizzes/{quizId}/start', [StudentQuizController::class, 'startQuiz']);

    Route::post('enroll-course', [EnrollmentController::class, 'enroll_course']);
    Route::post('enroll-class', [EnrollmentController::class, 'enroll_class']);

    Route::get('enrollment-status/{course_id}', [EnrollmentController::class, 'enrollmentStatus']);

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
    Route::post('Ask_us', 'ContactUsController@store');


});
