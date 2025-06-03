<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ClassDetailsResource;
use App\Http\Resources\Api\ClassesDetailsResource;
use App\Http\Resources\Api\GovernmentsResource;
 use App\Http\Resources\Api\CourseDetailsResource;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\VideoResource;


use App\Models\Category;
 use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Government;
use App\Models\Student_rate;
 use App\Models\NewsLetter;

use App\Models\Slider;

 use Illuminate\Http\Request;

class CourseController extends Controller
{

     public function getCoursesByCategory(Request $request)
    {

        $categoryId = $request->query('category_id');

        if (!$categoryId) {
            return $this->error('Category ID is required', 400);
        }

        $category = Category::find($categoryId);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        // Assuming Category has a relation `courses`
        $courses = $category->courses()->where('is_active', 1)->get();

        // You might want to use a CourseResource here
        return $this->success('', CoursesDetailsResource::collection($courses));
    }

   public function getCoursesById($id)
{
    $course = Course::where('id', $id)
                    ->where('is_active', 1)
                    ->first();

    if (!$course) {
        return $this->failure('Course not found or unpublished');
    }


    return $this->success('',         new CourseDetailsResource($course));

}

   public function getClassesByCoursesId($id)
    {
        $class = CourseClass::where('course_id', $id)
                        ->where('is_active', 1)
                        ->get();

        if (!$class) {
            return $this->failure('Class not found or unpublished');
        }


        return $this->success('',         ClassesDetailsResource::collection($class));
    }

   public function getClassById($id)
    {
        $class = CourseClass::where('is_active', 1)->find($id);
         if (!$class) {
            return $this->failure('Class not found or unpublished');
        }

        return $this->success('',         new ClassDetailsResource($class));

    }

public function getVideosByCourse($id)
{
    // Find the course by ID and make sure it's published
    $course = Course::where('id', $id)
                    ->where('is_active', 1)
                    ->first();

    if (!$course) {
        return $this->failure('Course not found or unpublished') ;
    }

    // Get videos related to this course (assuming relation `videos`)
    $videos = $course->videos()->get();

    // Return the videos collection as JSON, optionally use a resource collection if you have one
    return $this->success('',VideoResource::collection($videos) );
}






    public function getgovernments()
    {
        $cities = Government::get();

        return $this->success('', GovernmentsResource::collection($cities));
    }




public function getAboutUs()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';
    $ask_us = [
            'image_url'=>getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
            'label'           => setting('label' . $suffix),
            'description'     => setting('about_us' . $suffix),
            'experince_year'     => 20,
            'lecture_count'     => 200,
        ];
    $data = [
        'ask_us' =>$ask_us,


        'our_mission'    => setting('our_mission' . $suffix),
        'our_vision'     => setting('our_vission' . $suffix), // double-check spelling
    ];

    return $this->success('', $data);
}



public function getprivacypolicy()
{
    $locale = app()->getLocale(); // e.g., 'ar' or 'en'
    $key = 'privacy_policy_' . $locale; // Will resolve to 'privacy_policy_ar' or 'privacy_policy_en'

    $data = setting($key); // Fetch the appropriate setting

    return $this->success('', $data);
}


public function getfooter()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    $data = [

            'logo' => getImagePathFromDirectory(setting('common_question_banner'), 'Settings') ,
            'Site_name'           => setting('label_about_us' . $suffix),
            'Site_description'           => setting('label_about_us' . $suffix),

            'description'     => setting('description_about_us' . $suffix),
            'instagram_link'   => setting('instagram_link'),
            'facebook_link'    => setting('facebook_link'),
            'youtube_link'     => setting('youtube_link'),
            'telegram_link'     => setting('youtube_link'),
            'tiktok_link'     => setting('youtube_link'),
            'whatsapp_number'  => setting('whatsapp_number'),
            'sms_number'       => setting('sms_number'),

    ];

    return $this->success('', $data);
}



}
