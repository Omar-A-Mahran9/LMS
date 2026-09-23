<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\ClassDetailsResource;
use App\Http\Resources\Api\ClassesDetailsResource;
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
use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Models\Government;
use App\Models\QuizAttempt;
use App\Models\Student_rate;
 use App\Models\NewsLetter;

use App\Models\Slider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
// الموسم الدراسي بيبدأ في شهر يوليو (أول ما الكورسات بتبدأ)، والأبطال بيتصفروا مع بداية كل موسم
protected const ACADEMIC_YEAR_START_MONTH = 7;

// بداية الموسم الحالي: لو إحنا قبل يوليو يبقى الموسم بدأ يوليو السنة اللي فاتت
protected function academicYearStart(): Carbon
{
    $start = now()->startOfYear()->month(self::ACADEMIC_YEAR_START_MONTH);

    return $start->isFuture() ? $start->subYear() : $start;
}

public function topHeroesByCategory(Request $request)
{
    $categoryId = $request->integer('category_id') ?: null;
    $classId = $request->integer('class_id') ?: null;
    $quizId = $request->integer('quiz_id') ?: null;
    $limit = 10;

    $category = Category::where('is_publish', 1)
        ->when($categoryId, fn($q) => $q->where('id', $categoryId))
        ->first();

    $topStudents = collect();

    // 1) كويزات كورسات الصف (المرتبطة بالكورس مباشرة أو عن طريق الحصص)
    if ($category) {
        $courseIds = $category->courses()->pluck('id');

        $topStudents = $this->rankHeroes(
            QuizAttempt::whereHas('quiz', function ($q) use ($courseIds, $classId, $quizId) {
                $q->where(function ($q) use ($courseIds) {
                    $q->whereIn('course_id', $courseIds)
                        ->orWhereHas('class', fn($c) => $c->whereIn('course_id', $courseIds));
                })
                    ->when($classId, fn($q) => $q->where('class_id', $classId))
                    ->when($quizId, fn($q) => $q->where('id', $quizId));
            }),
            $limit
        );

        // 2) لو مفيش: كل كويزات طلاب الصف ده
        if ($topStudents->isEmpty()) {
            $topStudents = $this->rankHeroes(
                QuizAttempt::whereHas('student', fn($q) => $q->where('category_id', $category->id)),
                $limit
            );
        }
    }

    // 3) لو لسه مفيش: الأوائل على مستوى المنصة كلها
    if ($topStudents->isEmpty()) {
        $topStudents = $this->rankHeroes(QuizAttempt::query(), $limit);
    }

    $yearStart = $this->academicYearStart();

    return $this->success('', [
        'image' => getImagePathFromDirectory(setting('contact_banner'), 'Settings'),
        'academic_year' => $yearStart->year . '/' . ($yearStart->year + 1),
        'topStudents' => $topStudents,
    ]);
}

/**
 * ترتيب الطلاب: أفضل محاولة لكل طالب في كل كويز، ثم مجموع الدرجات / مجموع الدرجات النهائية
 * والترتيب بالنسبة المئوية ثم مجموع الدرجات.
 */
protected function rankHeroes($attemptsQuery, int $limit = 10)
{
    // طلبة الموسم الحالي بس: امتحانات السنة اللي فاتت متتحسبش
    $attempts = $attemptsQuery
        ->whereNotNull('score')
        ->where('created_at', '>=', $this->academicYearStart())
        ->with([
            'student.category',
            'quiz' => fn($q) => $q->withSum('questions as full_mark', 'points'),
        ])
        ->get()
        ->filter(fn($attempt) => $attempt->student && $attempt->quiz && !$this->isTestStudent($attempt->student));

    $stats = $attempts
        ->groupBy('student_id')
        ->map(function ($studentAttempts) {
            $student = $studentAttempts->first()->student;
            $score = 0;
            $possible = 0;

            foreach ($studentAttempts->groupBy('quiz_id') as $quizAttempts) {
                $best = $quizAttempts->sortByDesc('score')->first();
                $score += (int) $best->score;
                // لو الكويز مفيهوش درجات للأسئلة، نعتبر الدرجة النهائية = درجة الطالب عشان منقسمش على صفر
                $possible += max((int) $best->quiz->full_mark, (int) $best->score);
            }

            return [
                'student' => $student,
                'score' => $score,
                'full_score' => $possible,
                'percentage' => $possible > 0 ? round($score / $possible * 100, 2) : 0,
                'quizzes_count' => $studentAttempts->pluck('quiz_id')->unique()->count(),
                'attempts' => $studentAttempts->count(),
            ];
        });

    // عشان طالب حل كويز واحد بس ميسبقش طالب حل كويزات كتير بنسبة عالية:
    // اللي حل على الأقل نص عدد كويزات أكتر طالب بيتقدم الأول
    $minQuizzes = max(1, (int) ceil($stats->max('quizzes_count') / 2));

    return $stats
        ->sort(fn($a, $b) => [$b['quizzes_count'] >= $minQuizzes, $b['percentage'], $b['score']]
            <=> [$a['quizzes_count'] >= $minQuizzes, $a['percentage'], $a['score']])
        ->take($limit)
        ->values()
        ->map(fn($item, $index) => [
            'rank' => $index + 1,
            'student_id' => $item['student']->id,
            'name' => trim($item['student']->first_name . ' ' . $item['student']->last_name),
            'image' => $item['student']->full_image_path,
            'category' => $item['student']->category->name ?? 'N/A',
            'attempts' => $item['attempts'],
            'quizzes_count' => $item['quizzes_count'],
            'score' => $item['score'],
            'average_score' => $item['score'], // kept for older clients
            'full_score' => $item['full_score'],
            'percentage' => $item['percentage'],
        ]);
}

protected function isTestStudent($student): bool
{
    return str_contains(strtolower($student->first_name ?? ''), 'test')
        || str_contains(strtolower($student->last_name ?? ''), 'test')
        || str_contains(strtolower($student->email ?? ''), 'test');
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
                'phone_number_two'       => setting('whatsapp_number'),

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
 $classes_by_code = CourseClass::where('is_active', 1)
    ->latest('id') // or latest('created_at')
    ->take(10)
    ->get();

        return $this->success('', [
            'sliders' => SliderResource::collection($sliders),
            'about_us'=> $about,
            'categories' => CategoryResource::collection($categories),
            'rates' => RateResource::collection($rates),
            'heroes_by_category' => $heroesByCategory,
            'classes_by_code' => ClassesDetailsResource::collection($classes_by_code),

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
