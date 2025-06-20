<?php

use App\Http\Controllers\API\BookOrderController;
use App\Http\Controllers\Dashboard\EnrollmentController;
use App\Http\Controllers\Dashboard\HomeworkByClassController;
use App\Http\Controllers\Dashboard\QuizByClassController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get("/", "DashboardController@index")->name('index');
/* begin Delete And restore */
Route::delete("admins/delete-selected", "AdminController@deleteSelected");
Route::get("admins/restore-selected", "AdminController@restoreSelected");
Route::delete("categories/delete-selected", "CategoryController@deleteSelected");
Route::get("categories/restore-selected", "CategoryController@restoreSelected");
Route::delete("contact-requests/delete-selected", "ContactRequestController@deleteSelected");
Route::delete("students/delete-selected", "StudentController@deleteSelected");
Route::delete("cities/delete-selected", "GovernmentsController@deleteSelected");
Route::get("cities/restore-selected", "GovernmentsController@restoreSelected");
Route::delete("newsletter/delete-selected", "NewsLetterController@deleteSelected");
 Route::delete("Course/delete-selected", "CourseController@deleteSelected");
 Route::get("Course/restore-selected", "CourseController@restoreSelected");
 Route::delete("Course/delete-selected", "CourseController@deleteSelected");
 Route::get("Course/restore-selected", "CourseController@restoreSelected");
 Route::delete("whyus/delete-selected", "WhyusController@deleteSelected");
 Route::get("whyus/restore-selected", "WhyusController@restoreSelected");
 Route::delete("CommonQuestion/delete-selected", "CommonQuestionController@deleteSelected");
 Route::get("CommonQuestion/restore-selected", "CommonQuestionController@restoreSelected");
 Route::delete("howuse/delete-selected", "HowuseController@deleteSelected");
 Route::get("howuse/restore-selected", "HowuseController@restoreSelected");
 Route::delete("enrollments/delete-selected", "EnrollmentController@deleteSelected");
 Route::get("enrollments/restore-selected", "EnrollmentController@restoreSelected");

 Route::resource('courses', 'CourseController')->except(['create', 'edit']);
 Route::resource('videos', 'CourseVideoController')->except(['create', 'edit']);
 Route::get('classes/{classId}/videos', 'CourseVideoController@getvideosbyclasses');
 Route::resource('classes', 'ClassController')->except(['create', 'edit']);
  Route::resource('sections', 'SectionController')->except(['create', 'edit']);

 Route::resource('books', 'BookController')->except(['create', 'edit']);
 Route::resource('quizzes', 'QuizController')->except(['create', 'edit']);
 Route::resource('classes.quizzes', QuizByClassController::class);
  Route::resource('sections.quizzes', QuizBySectionController::class);

  Route::resource('classes.homeworks', HomeworkByClassController::class);

 Route::resource('homeworks', 'HomeWorkController')->except(['create', 'edit']);
 Route::resource('questions', 'QuestionController')->except(['create', 'edit']);
 Route::resource('homeworks-questions', 'HomeWorkQuestionController')->except(['create', 'edit']);
 Route::resource('whyus', 'WhyusController')->except(['create', 'edit']);
 Route::resource('howuse', 'HowuseController')->except(['create', 'edit']);
 Route::resource('CommonQuestion', 'CommonQuestionController')->except(['create', 'edit']);
/** begin resources routes **/
 Route::resource('admins', 'AdminController')->except(['create', 'edit']);
Route::resource('booking', 'BookingController')->except(['create', 'edit']);
Route::resource('brands', 'BrandController')->except(['create', 'edit']);
Route::resource('award', 'BrandController')->except(['create', 'edit']);
Route::resource('partner', 'PartenerController')->except(['create', 'edit']);
Route::resource('gallary', 'GallaryController')->except(['create', 'edit']);
Route::resource('enrollments', 'EnrollmentController')->except(['create', 'edit']);
Route::resource('orders', 'OrderController')->except(['create', 'edit']);

Route::get('/enrollments/courses-for-student/{student}', [EnrollmentController::class, 'getCoursesForStudent']);
Route::post('/enrollments/{id}/status', [EnrollmentController::class, 'changeStatus']);
Route::post('/orders/{id}/status', [BookOrderController::class, 'changeStatus']);


Route::resource('governments', 'GovernmentsController')->except(['create', 'edit']);
Route::resource('categories', 'CategoryController')->except(['create', 'edit']);
Route::resource('maincategories', 'MainCategoryController')->except(['create', 'edit']);

Route::resource('contact-requests', 'ContactRequestController')->except(['create', 'edit', 'store', 'update']);
Route::resource('students', 'StudentController')->except(['create', 'edit']);
Route::resource('students_rate', 'StudentsRatesController')->except(['create', 'edit']);

Route::get('students/blocking/{student}', 'StudentController@blocked')->name('students.blocked');
Route::get('students/blocked-selected', 'StudentController@blockedSelected');


Route::resource('sliders', 'SliderController');

Route::resource('newsletter', 'NewsLetterController')->only(['index', 'destroy']);
Route::get('profile-info', 'ProfileController@profileInfo')->name('profile-info');
Route::put('update-profile-info', 'ProfileController@updateProfileInfo')->name('update-profile-info');
Route::put('update-profile-email', 'ProfileController@updateProfileEmail')->name('update-profile-email');
Route::put('update-profile-password', 'ProfileController@updateProfilePassword')->name('update-profile-password');

/**  ====================SETTINGS======================  **/
Route::prefix('settings')->name('settings.')->group(function () {
    Route::match(['get', 'post'], 'general/main', 'SettingController@main')->name('general.main');
    Route::match(['get', 'post'], 'general/terms', 'SettingController@terms')->name('general.terms');
    Route::match(['get', 'post'], 'general/contact', 'SettingController@contact')->name('general.contact');
    Route::match(['get', 'post'], 'general/mobile-app', 'SettingController@mobileApp')->name('general.mobile_app');
    Route::match(['get', 'post'], 'general/tax', 'SettingController@tax')->name('general.tax');

    Route::resource('roles', 'RoleController');
    Route::get('role/{role}/admins', 'RoleController@admins');

    Route::match(['get', 'post'], 'home-content/main', 'HomeController@index')->name('home-content');
    Route::match(['get', 'post'], 'home-content/about-us', 'HomeController@aboutUs')->name('home.about-us');
    Route::match(['get', 'post'], 'home-content/terms', 'HomeController@terms')->name('home.terms');
    Route::match(['get', 'post'], 'home-content/privacy-policy', 'HomeController@privacyPolicy')->name('home.privacy-policy');
    Route::match(['get', 'post'], 'home-content/our-mission', 'HomeController@ourmission')->name('home.our-mission');
    Route::match(['get', 'post'], 'home-content/our-vission', 'HomeController@ourvission')->name('home.our-vission');
    Route::match(['get', 'post'], 'home-content/banner', 'HomeController@banner')->name('home.banner');


 });

Route::get('trash/{modelName}/{id}/restore', 'TrashController@restore')->name('trash.restore');
Route::get('trash/{modelName?}', 'TrashController@index')->name('trash');
Route::get('trash/{modelName}/{id}', 'TrashController@restore');
Route::get('/language/{lang}', function (Request $request) {
    session()->put('locale', $request->lang);
    return redirect()->back();
})->name('change-language');
/** notifications routes **/
Route::post('/save-token', 'NotificationController@saveToken')->name('save-token');
Route::post('/send-notification', 'NotificationController@sendNotification')->name('send.notification');
Route::get('notifications/{id}/mark_as_read', 'NotificationController@markAsRead')->name('notifications.mark_as_read');
Route::get('notifications/{type}/load-more/{next}', 'NotificationController@loadMore')->name('notifications.load_more');
Route::get('notifications/mark-all-as-read', 'NotificationController@markAllAsRead')->name('notifications.mark_all_as_read');
Route::post('/fetch-data', 'DashboardController@ordersTransaction')->name('fetch.data');
