<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\GovernmentsResource;
use App\Http\Resources\Api\CommonQuestionResource;
use App\Http\Resources\Api\CourseDetailsResource;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\CoursesFeaturedResource;
use Illuminate\Support\Str;

use App\Http\Resources\Api\RateResource;
 use App\Http\Resources\Api\SliderResource;
use App\Models\Book;
use App\Models\Category;
 use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\Government;
use App\Models\Student_rate;
 use App\Models\NewsLetter;

use App\Models\Slider;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function topHeroesByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');

        $category = Category::where('is_publish', 1)
            ->whereNull('parent_id')
            ->when($categoryId, fn($q) => $q->where('id', $categoryId))
            ->with(['courses.classes.quizzes.attempts.student'])
            ->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $studentScores = [];

        foreach ($category->courses as $course) {
            foreach ($course->classes as $class) {
                foreach ($class->quizzes as $quiz) {
                    foreach ($quiz->attempts as $attempt) {
                        if (!$attempt->student) continue;

                        $studentId = $attempt->student_id;

                        if (!isset($studentScores[$studentId])) {
                            $studentScores[$studentId] = [
                                'total_score' => 0,
                                'attempts' => 0,
                                'student' => $attempt->student,
                            ];
                        }

                        $studentScores[$studentId]['total_score'] += $attempt->score;
                        $studentScores[$studentId]['attempts'] += 1;
                    }
                }
            }
        }

        $topStudents = collect($studentScores)
            ->map(function ($data) {
                $average = $data['attempts'] > 0
                    ? $data['total_score'] / $data['attempts']
                    : 0;
                $full = $data['attempts'] * 100; // Adjust if quiz full mark is different
                return [
                    'name' => $data['student']->name,
                    'image' => $data['student']->full_image_path ,
                    'category' => $data['student']->category->name ?? 'N/A', // optional fallback
                    'average_score' => round($average, 2),
                    'full_score' => $full,
                ];
            })
            ->sortByDesc('average_score')
            ->take(10)
            ->values();
        // 🧪 If no data, return dummy values
        if ($topStudents->isEmpty()) {
            $topStudents = collect([
                [
                    'name' => 'Dummy Student 1',
                    'image' => asset('images/dummy1.jpg'),
                    'category' => $category->name,
                    'average_score' => 95.5,
                    'full_score' => 100,
                ],
                [
                    'name' => 'Dummy Student 2',
                    'image' => asset('images/dummy2.jpg'),
                    'category' => $category->name,
                    'average_score' => 93.0,
                    'full_score' => 100,
                ],
                [
                    'name' => 'Dummy Student 3',
                    'image' => asset('images/dummy3.jpg'),
                    'category' => $category->name,
                    'average_score' => 91.2,
                    'full_score' => 100,
                ],
            ]);
        }

        return $this->success('', [
            'image' => asset('images/dummy3.jpg'),
            'topStudents' => $topStudents,
        ]);

    }

    public function getHome()
    {
        $locale = app()->getLocale();
        $suffix = $locale === 'ar' ? '_ar' : '_en';

        // Sliders
        $sliders = Slider::where('status', '1')->get();
    $categories = Category::where('is_publish', 1)
            ->whereNull('parent_id') // Only main categories; remove this to get all
            ->get();
    $rates = Student_rate::all(); // Or you can use a query like ->where('status', 'approved') to filter rate
        $address = $locale === 'ar' ? setting('address_ar') : setting('address_en');

        $ask_us = [
                'image_url'=>getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
                'label'           => setting('label' . $suffix),
                'description'     => setting('about_us' . $suffix),
                'experince_year'     => 20,
                'lecture_count'     => 200,
            ];
    $HowUse = [
                'label'           => setting('label_how_to_use' . $suffix),
                'description'     => setting('description_how_to_use' . $suffix),
                'image_url' => getImagePathFromDirectory(setting('how_to_use_banner'), 'Settings') ,
                'video_url' => convertToYoutubeEmbed(setting('video_how_to_use_url')),


            ];
            $books = Book::where('is_active', 1)
                ->where('is_featured', 1)
                ->get();
            $CommonQuestion = CommonQuestion::get();
            $contact_us_data=[
                'label'           => setting('label_about_us' . $suffix),
                'description'     => setting('description_about_us' . $suffix),
                'phone_number'       => setting('sms_number'),
                'email'            => setting('email'),
                'address'          => $address,
                'google_map_url' =>  setting('google_map_url'),

            ];
        // Combine and return
    $heroesByCategory = Category::where('is_publish', 1)
    ->whereNull('parent_id') // فقط التصنيفات الرئيسية
    ->with(['courses.classes.quizzes.attempts.student']) // eager load all necessary levels
    ->get()
    ->map(function ($category) {
        $studentScores = [];

        foreach ($category->courses as $course) {
            foreach ($course->classes as $class) {
                foreach ($class->quizzes as $quiz) {
                    foreach ($quiz->attempts as $attempt) {
                        $studentId = $attempt->student_id;

                        if (!isset($studentScores[$studentId])) {
                            $studentScores[$studentId] = [
                                'total_score' => 0,
                                'attempts' => 0,
                                'student' => $attempt->student
                            ];
                        }

                        $studentScores[$studentId]['total_score'] += $attempt->score;
                        $studentScores[$studentId]['attempts'] += 1;
                    }
                }
            }
        }

        $students = collect($studentScores)
            ->map(function ($data) {
                $data['average'] = $data['attempts'] > 0
                    ? $data['total_score'] / $data['attempts']
                    : 0;
                return $data;
            })
            ->sortByDesc('average')
            ->take(10)
            ->values();

        return [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'heroes' => $students->map(function ($item) {
                return [
                    'student_id' => $item['student']->id,
                    'name' => $item['student']->name,
                    'average_score' => round($item['average'], 2),
                    // يمكنك إضافة صورة الطالب أو بيانات إضافية هنا
                ];
            }),
        ];
    });

    $today = Carbon::today()->toDateString(); // أو ->now() لو فيه وقت

    $featured_courses = Course::where('is_active', 1)
        ->where('featured', 1)
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
        ->get();

    $courses = Course::where('is_active', 1)
        ->where('is_class', 0)->where('category_id',null)
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
        ->get();


        return $this->success('', [
            'sliders' => SliderResource::collection($sliders),
            'categories' => CategoryResource::collection($categories),
            'rates' => RateResource::collection($rates),
            'heroes_by_category' => $heroesByCategory,

            'ask_us' =>$ask_us,
            'HowUse' =>$HowUse,
            'featured_courses' => CoursesFeaturedResource::collection($featured_courses),

            'CommonQuestion' =>[
                'label'           => setting('label_common_question' . $suffix),
                'description'     => setting('description_common_question' . $suffix),
                'image_url' => getImagePathFromDirectory(setting('common_question_banner'), 'Settings') ,
                'question_and_answer'=>CommonQuestionResource::collection($CommonQuestion),
            ],
            'contact_us_data'=>$contact_us_data,
            'courses' => CoursesFeaturedResource::collection($courses),

            'Books' =>BookResource::collection($books),

    ]);
    }

protected function convertToIframe($url)
{
    // If already iframe, just return it
    if (Str::contains($url, '<iframe')) {
        return $url;
    }

    // Fallback iframe using q param (not perfect, but works)
    return '<iframe
                src="https://www.google.com/maps?q=' . urlencode($url) . '&output=embed"
                width="600"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>';
}


    public function newsLetter(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'unique:news_letters'],
        ]);

        NewsLetter::create([
            'email' => $request->email
        ]);

        return $this->success(__('Created Successfully'));
    }



    public function getCategory()
    {
        $categories = Category::where('is_publish', 1)
        ->whereNull('parent_id') // Only main categories; remove this to get all
        ->get();

        return $this->success('', CategoryResource::collection($categories));
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
