<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\GovernmentsResource;
use App\Http\Resources\Api\CommonQuestionResource;

use App\Http\Resources\Api\CoursesFeaturedResource;
use Illuminate\Support\Str;

use App\Http\Resources\Api\RateResource;
 use App\Http\Resources\Api\SliderResource;
use App\Models\Admin;
use App\Models\Book;
use App\Models\Category;
 use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\Government;
use App\Models\Student_rate;
 use App\Models\NewsLetter;

use App\Models\Slider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // public function topHeroesByCategory(Request $request)
    // {
    //     $categoryId = $request->get('category_id');

    //     $category = Category::where('is_publish', 1)
    //         ->whereNull('parent_id')
    //         ->when($categoryId, fn($q) => $q->where('id', $categoryId))
    //         ->with(['courses.classes.quizzes.attempts.student'])
    //         ->first();
    //     if (!$category) {
    //         return $this->failure('Category not found');
    //     }

    //     $studentScores = [];

    //     foreach ($category->courses as $course) {
    //         foreach ($course->classes as $class) {
    //             foreach ($class->quizzes as $quiz) {
    //                 foreach ($quiz->attempts as $attempt) {
    //                     if (!$attempt->student) continue;

    //                     $studentId = $attempt->student_id;

    //                     if (!isset($studentScores[$studentId])) {
    //                         $studentScores[$studentId] = [
    //                             'total_score' => 0,
    //                             'attempts' => 0,
    //                             'student' => $attempt->student,
    //                         ];
    //                     }

    //                     $studentScores[$studentId]['total_score'] += $attempt->score;
    //                     $studentScores[$studentId]['attempts'] += 1;
    //                 }
    //             }
    //         }
    //     }

    //     $topStudents = collect($studentScores)
    //         ->map(function ($data) {
    //             $average = $data['attempts'] > 0
    //                 ? $data['total_score'] / $data['attempts']
    //                 : 0;
    //             $full = $data['total_score']; // Adjust if quiz full mark is different
    //             return [
    //                 'name' => $data['student']->first_name ." ". $data['student']->last_name,
    //                 'image' => $data['student']->full_image_path ,
    //                 'category' => $data['student']->category->name ?? 'N/A', // optional fallback
    //                 'average_score' => round($average, 2),
    //                 'full_score' => $full,
    //             ];
    //         })
    //         ->sortByDesc('average_score')
    //         ->take(10)
    //         ->values();
    //     // 🧪 If no data, return dummy values
    //     // if ($topStudents->isEmpty()) {
    //     //     $topStudents = collect([
    //     //         [
    //     //             'name' => 'Dummy Student 1',
    //     //             'image' => getImagePathFromDirectory('','Students'),
    //     //             'category' => $category->name,
    //     //             'average_score' => 95.5,
    //     //             'full_score' => 100,
    //     //         ],
    //     //         [
    //     //             'name' => 'Dummy Student 2',
    //     //             'image' =>  getImagePathFromDirectory('','Students'),
    //     //             'category' => $category->name,
    //     //             'average_score' => 93.0,
    //     //             'full_score' => 100,
    //     //         ],
    //     //         [
    //     //             'name' => 'Dummy Student 3',
    //     //             'image' => getImagePathFromDirectory('','Students'),
    //     //             'category' => $category->name,
    //     //             'average_score' => 91.2,
    //     //             'full_score' => 100,
    //     //         ],
    //     //     ]);
    //     // }

    //     return $this->success('', [
    //         'image' =>  getImagePathFromDirectory('','Students'),
    //         'topStudents' => $topStudents,
    //     ]);

    // }
public function topHeroesByCategory(Request $request)
{
    $categoryId = $request->get('category_id');
    $classId    = $request->get('class_id');
    $quizId     = $request->get('quiz_id');

    // Get the root or selected category
    $category = Category::where('is_publish', 1)
        ->whereNull('parent_id')
        ->when($categoryId, fn($q) => $q->where('id', $categoryId))
        ->with([
            'courses.classes.quizzes.questions',
            'courses.classes.quizzes.attempts.student.category'
        ])
        ->first();

    if (!$category) {
        return $this->failure('Category not found');
    }

    $studentStats = [];

    foreach ($category->courses as $course) {
        foreach ($course->classes as $class) {
            // Skip if class_id filter exists and doesn't match
            if ($classId && $class->id != $classId) continue;

            foreach ($class->quizzes as $quiz) {
                // Skip if quiz_id filter exists and doesn't match
                if ($quizId && $quiz->id != $quizId) continue;

                $quizFullMark = $quiz->questions->sum('points') ?? 100;

                foreach ($quiz->attempts as $attempt) {
                    $student = $attempt->student;

                    if (!$student) continue;

                    // Skip test accounts
                    if (
                        str_contains(strtolower($student->first_name), 'test') ||
                        str_contains(strtolower($student->last_name), 'test') ||
                        str_contains(strtolower($student->email ?? ''), 'test')
                    ) {
                        continue;
                    }

                    $studentId = $student->id;

                    if (!isset($studentStats[$studentId])) {
                        $studentStats[$studentId] = [
                            'total_score' => 0,
                            'total_possible' => 0,
                            'attempts' => 0,
                            'student' => $student,
                        ];
                    }

                    $studentStats[$studentId]['total_score'] += $attempt->score;
                    $studentStats[$studentId]['total_possible'] += $quizFullMark;
                    $studentStats[$studentId]['attempts'] += 1;
                }
            }
        }
    }

    $topStudents = collect($studentStats)
        ->filter(fn($data) => $data['attempts'] > 0)
        ->map(function ($data) {
            $data['average'] = $data['total_score'] / $data['attempts'];
            $data['percentage'] = $data['total_possible'] > 0
                ? ($data['total_score'] / $data['total_possible']) * 100
                : 0;
            return $data;
        })
        ->sort(function ($a, $b) {
            if ($a['attempts'] == $b['attempts']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $a['attempts'] <=> $b['attempts'];
        })
        ->take(10)
        ->values()
        ->map(function ($item) {
            return [
                'student_id' => $item['student']->id,
                'name' => $item['student']->first_name . ' ' . $item['student']->last_name,
                'image' => $item['student']->full_image_path,
                'category' => $item['student']->category->name ?? 'N/A',
                'attempts' => $item['attempts'],
                'average_score' => round($item['average'], 2),
                'percentage' => round($item['percentage'], 2),
                'full_score' => $item['total_score'],
                'total_possible' => $item['total_possible'],
            ];
        });

    return $this->success('', [
        'image' => getImagePathFromDirectory(setting('contact_banner'), 'Settings'),
        'topStudents' => $topStudents,
    ]);
}

    public function getHome()
    {
        $locale = app()->getLocale();
        $suffix = $locale === 'ar' ? '_ar' : '_en';

    $about = [
            'image_url'=>getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
            'label'           => setting('label' . $suffix),
            'description'     => setting('about_us' . $suffix),
            'experince_year' => Admin::where('id',3)->value('experience_years') ?? 20,
            'lecture_count' => CourseVideo::count(),
        ];


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
                    'lecture_count' => CourseVideo::count(),
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

    $featured_courses = Course::where('is_active', 1)->where('is_enrollment_open', 1)
        ->where('show_in_home', 1)
        ->where('featured', 1)
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
->withCount(['enrollments' => function ($q) {
        $q->where('status', 'approved');
    }])
    ->orderBy('max_students', 'asc')
    ->get()
    ->filter(function ($course) {
        return is_null($course->max_students) || $course->enrollments_count <= $course->max_students;
    })
    ->values()->take(6);

$courses = Course::where('is_active', 1)
    ->where('show_in_home', 1)
    ->where('is_enrollment_open', 1)
    ->where('is_class', 0)
    ->whereNull('category_id')
    ->whereDate('start_date', '<=', $today)
    ->whereDate('end_date', '>=', $today)
    ->withCount(['enrollments' => function ($q) {
        $q->where('status', 'approved');
    }])
    ->orderBy('max_students', 'asc')
    ->get()
    ->filter(function ($course) {
        return is_null($course->max_students) || $course->enrollments_count <= $course->max_students;
    })
    ->values()->take(6);  // get latest 6
 // reindex
        return $this->success('', [
            'sliders' => SliderResource::collection($sliders),
            'about_us'=> $about,
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
            'experince_year' => Admin::where('id',3)->value('experience_years') ?? 20,
            'lecture_count' => CourseVideo::count(),
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

            'logo' => getImagePathFromDirectory(setting('light_logo_image'), 'Settings') ,
            'Site_name'           => setting('label_about_us' . $suffix),
            'Site_description'           => setting('label_about_us' . $suffix),

            'description'     => setting('description_about_us' . $suffix),
            'instagram_link'   => setting('instagram_link'),

            'ios_link'   => setting('instagram_link'),
            'google_play_link'   => setting('instagram_link'),

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
