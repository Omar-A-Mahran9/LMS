<?php

use App\Http\Controllers\Api\BookOrderController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StudentHomeworkController;
use App\Http\Controllers\Api\StudentQuizController;
use App\Http\Controllers\CertificateController as ControllersCertificateController;
use App\Http\Controllers\Dashboard\SectionController;
use App\Http\Controllers\Dashboard\SectionVideoController;
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
        Route::get('certificate/{id}', [CertificateController::class, 'publicDownload'])
    ->name('certificate.public');
        Route::get('all_classes', 'CourseController@getAllClasses');

Route::middleware(['auth:api'])->group(function () {
    Route::get('classes_by_courses_id/{id}', 'CourseController@getClassesByCoursesId');
    Route::get('get_rates_for_course/{course_id}', 'CourseController@getRatesForCourse');
    Route::post('student_rates_for_course', 'CourseController@storerate');
    Route::get('videos_by_course/{id}', 'CourseController@getVideosBySections');
    Route::get('check_course/{id}', 'CourseController@checkCourseAccess');

    Route::get('videos_by_classes/{id}', 'CourseController@getVideosByClass');
    Route::post('videos/{video}/watch', 'CourseController@logWatch');
    Route::get('class/{id}', 'CourseController@getClassById');
    Route::get('quiz_by_class_id/{id}', 'CourseController@getQuizClassById');
    Route::get('quiz/{id}', 'CourseController@getQuizById');
    Route::get('quizzes/{quizId}/start', [StudentQuizController::class, 'startQuiz']);
    Route::post('student-quizzes/{quizAttemptId}/submit', [StudentQuizController::class, 'submitQuiz']);
    Route::get('student-quizzes/{studentQuizId}/results', [StudentQuizController::class, 'results']);
    Route::get('homeworks/{homeworkId}/start', [StudentHomeworkController::class, 'startHomework']);
    Route::post('student-homeworks/{homeworkAttemptId}/submit', [StudentHomeworkController::class, 'submitHomework']);
    Route::get('student-homeworks/{studentHomeworkId}/results', [StudentHomeworkController::class, 'results']);
    Route::post('enroll-course', [EnrollmentController::class, 'enroll_course']);
    Route::post('enroll-class', [EnrollmentController::class, 'enroll_class']);
    Route::get('enrollment-status/{course_id}', [EnrollmentController::class, 'enrollmentStatus']);
    Route::get('books/{id}', 'BookController@show');
  Route::get('certificate/verify/{id}', [CertificateController::class, 'verify'])
    ->name('certificates.verify');
    Route::get('certificates/{course}', [CertificateController::class, 'download'])
        ->name('student.certificates.download');

    Route::get('profile-info', 'ProfileController@profileInfo')->name('profile-info');
    Route::post('update-profile-info', 'ProfileController@updateProfileInfo')->name('update-profile-info');
     Route::post('logout', [ProfileController::class, 'logout'])->name('logout');
    Route::post('logout-all', [ProfileController::class, 'logoutFromAllDevices'])->name('logout-all');;
    Route::put('update-profile-password', [ProfileController::class, 'updatePassword']);
    Route::get('quizzes-results', [ProfileController::class, 'quizzesResults'])->name('quizzes.results');
    Route::get('homework-results', [ProfileController::class, 'homeworksResults'])->name('homeworks.results');
    Route::get('my-courses', [ProfileController::class, 'myCourses'])->name('courses');
    Route::get('stats', [ProfileController::class, 'studentStatistics'])->name('stats');

});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::get('/sections/{id}/videos/count', [SectionVideoController::class, 'countBySection']);
    Route::get('/courses/{id}/sections/count', [SectionController::class, 'countSectionByCourse']);


    Route::post('login', 'Auth\AuthController@login');
    Route::post('login-otp/{customer:phone}', 'Auth\AuthController@loginOTP');
    Route::post('register', 'Auth\AuthController@register');

    Route::post('send-otp', 'Auth\ForgetPasswordController@sendOtp');
    Route::post('verify-otp', 'Auth\ForgetPasswordController@checkOTP');
    Route::post('change-password', 'Auth\ForgetPasswordController@changePassword');
    Route::post('resend-otp', 'Auth\ForgetPasswordController@reSendOtp');
    Route::get('/heroes/by-category', [HomeController::class, 'topHeroesByCategory']);

    Route::get('about_us', 'HomeController@getAboutUs');
    Route::get('privacy_policy', 'HomeController@getprivacypolicy');
    Route::get('governments', 'HomeController@getgovernments');
    Route::get('home', 'HomeController@getHome');
    Route::get('general', 'GeneralInvokableController');

    Route::get('categories', 'HomeController@getCategory');
    Route::get('footer', 'HomeController@getfooter');
    Route::get('courses_by_category', 'CourseController@getCoursesByCategory');
    Route::get('courses', 'CourseController@getCourses');
    Route::get('courses_by_id/{id}', 'CourseController@getCoursesById');
     Route::get('books', 'BookController@index');
    Route::get('books/{id}', 'BookController@show');
    Route::post('/books/order', [BookOrderController::class, 'store']);

    Route::post('news-letter', 'HomeController@newsLetter');
    Route::post('Ask_us', 'ContactUsController@store');


});
