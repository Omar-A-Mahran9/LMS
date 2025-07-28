<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ClassDetailsResource;
use App\Http\Resources\Api\ClassesDetailsResource;
use App\Http\Resources\Api\GovernmentsResource;
 use App\Http\Resources\Api\CourseDetailsResource;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\CourseStatusResource;
use App\Http\Resources\Api\QuizResource;
use App\Http\Resources\Api\SectionResource;
use App\Http\Resources\Api\VideoResource;


use App\Models\Category;
use App\Models\ClassStudent;
use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Models\CourseVideoStudent;
use App\Models\Government;

use App\Models\Quiz;
use App\Models\Rate;
use App\Models\Section;
use App\Models\Student_rate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
public function getCoursesByCategory(Request $request)
{
    $categoryId = $request->query('category_id');
    $perPage = $request->query('per_page', 10);
    $filter = $request->query('filter'); // values: 'my', 'other', or null

    $query = Course::query()
        ->where('is_active', 1)
        ->where('is_class', 1)
        ->where('is_enrollment_open', 1)
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now());

    // فلترة حسب الفئة
    if ($categoryId) {
        $category = Category::find($categoryId);
        if (!$category) {
            return $this->error('Category not found', 404);
        }
        $query->where('category_id', $category->id);
    }

    // فلترة حسب الطالب إذا كان مسجل دخول
    if (Auth::guard('api')->check()) {
        $student = Auth::guard('api')->user();

        if ($filter === 'my') {
            // الكورسات اللي الطالب مشترك فيها فعليًا
            $query->whereHas('students', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->whereIn('course_student.status', ['approved', 'pending'])
                  ->where('course_student.is_active', 1);
            });
        } elseif ($filter === 'other') {
            // الكورسات اللي الطالب مش مشترك فيها أو مشترك بس حالته غير نشطة
            $query->whereDoesntHave('students', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->whereIn('course_student.status', ['approved', 'pending'])
                  ->where('course_student.is_active', 1);
            });
        }
    }

    $courses = $query->paginate($perPage);
    $resource = CoursesDetailsResource::collection($courses)->response()->getData(true);

    return $this->successWithPagination('Courses retrieved successfully.', $resource);
}



public function getCoursesById(Request $request, $id)
{
    $studentId = auth()->check() ? auth()->id() : null;

    $course = Course::where('id', $id)
        ->where('is_active', 1)
        ->where('is_enrollment_open', 1)
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now())
        ->first();

    if (
        !$course
    ) {
        return $this->failure('Course not found or full');
    }

    // Track views
    $viewerId = $studentId
        ? 'user_' . $studentId
        : 'device_' . $request->header('Device-Token', $request->ip());

    $cacheKey = "course_viewed_{$course->id}_{$viewerId}";
    if (!Cache::has($cacheKey)) {
        $course->increment('views');
        Cache::put($cacheKey, true, now()->addHours(6));
    }

    return $this->success('', new CourseDetailsResource($course));
}

public function getClassesByCoursesId(Request $request, $id)
{
    $student = auth('api')->user();
    $studentId=auth('api')->user()->id;
    if (!$student) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }


       // Ensure course has this student enrolled and is active
    $courseExists = Course::where('id', $id)
        ->where('is_active', 1)
        ->whereHas('enrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
              ->where('status', 'approved')
              ->where('is_active', 1);
        })
        ->first();

    if (!$courseExists) {
        return $this->failure('Course not found or unauthorized.');
    }
    $perPage = $request->query('per_page', 10);
$classes = CourseClass::where('course_id', $id)
    ->where('is_active', 1)
    ->whereHas('videos', function ($q) {
        $q->where('is_active', 1);
    })
    ->paginate($perPage);

    if ($classes->isEmpty()) {
        return $this->failure('Class not found or unpublished');
    }

    $resource = ClassesDetailsResource::collection($classes)->response()->getData(true);

    return $this->successWithPagination('', $resource);
}

public function getClassById($id)
{
    $class = CourseClass::where('is_active', 1)->find($id);
    if (!$class) {
        return $this->failure('Class not found or unpublished');
    }

    $student = auth()->user(); // assumes sanctum or jwt auth

    if ($student) {
        // Check if student already viewed this class
        $alreadyViewed = ClassStudent::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->exists();

        if (!$alreadyViewed) {
            // Log view and increment class views
            ClassStudent::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
            ]);

            $class->increment('views');
        }
    }

    return $this->success('', new ClassDetailsResource($class));
}


public function getQuizClassById($id)
    {
        $class = CourseClass::where('is_active', 1)->find($id);
         if (!$class) {
            return $this->failure('Class not found or unpublished');
        }

        return $this->success('',         new ClassDetailsResource($class));

    }

        public function getQuizById($id)
    {
        $data = Quiz::find($id);
         if (!$data) {
            return $this->failure('Quiz not found or unpublished');
        }

        return $this->success('',  new QuizResource($data));

    }


public function getVideosByClass($id)
{
    $studentId = Auth::id();
    $student = Auth::user();

    // Fetch class and check if it's active
    $class = CourseClass::where('id', $id)
        ->where('is_active', 1)
        ->first();

    if (!$class) {
        return $this->failure('Class not found or unpublished');
    }

    // Check if student is enrolled in the course
    $course = Course::where('id', $class->course_id)
        ->where('is_active', 1)
        ->whereHas('enrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
              ->where('status', 'approved')
              ->where('is_active', 1);
        })
        ->first();

    if (!$course) {
        return $this->failure('Course not found or unauthorized.');
    }

    // Optionally track class view (like section)
    $cacheKey = "class_viewed_{$class->id}_student_{$studentId}";
    if (!Cache::has($cacheKey)) {
        $class->increment('views');
        Cache::put($cacheKey, true, now()->addHours(6));
    }

    // Eager load videos and student progress
    $videos = $class->videos()
        ->where('is_active', 1)
        ->with(['studentProgress' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])
        ->get();

    // Format each video using the resource


    return $this->success('Class videos loaded', [
        // 'course_data' => [
        //     'course_id'           => $course->id,
        //     'course_title'        => $course->title,
        //     'has_certificate'     => $course->certificate_available,
        //     'certificate_url'     => $course->certificate_available
        //         ? getOrGeneratePublicCertificateUrl($student, $course)
        //         : null,
        //     'is_completed'        => $course->is_completed,
        //     'progress_percentage' => $course->progress_percentage,
        //     'has_rated'           => Student_rate::where('course_id', $course->id)
        //         ->where('student_id', $studentId)
        //         ->exists(),
        // ],
        'class_data' =>new ClassDetailsResource($class, $studentId),
    ]);
}

public function getVideosBySections($id)
{
    $studentId = Auth::id();
        $student = Auth::user();

    // Ensure course has this student enrolled and is active
    $courseExists = Course::where('id', $id)
        ->where('is_active', 1)
        ->whereHas('enrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
              ->where('status', 'approved')
              ->where('is_active', 1);
        })
        ->first();

    if (!$courseExists) {
        return $this->failure('Course not found or unauthorized.');
    }
    $sections = Section::with([
        // Videos and progress
        'videos' => function ($query) use ($studentId) {
            $query->where('is_active', 1)
                ->with(['studentProgress' => function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                }]);
        },
        'quizzes.questions',
        'homeworks.questions',
    ])
    ->where('course_id', $id)
    ->where('is_active', 1)
    ->get();
        foreach ($sections as $section) {
            // Section view cache key per student
            $cacheKey = "section_viewed_{$section->id}_student_{$studentId}";

            if (!Cache::has($cacheKey)) {
                $section->increment('views');
                Cache::put($cacheKey, true, now()->addHours(6)); // avoid re-counting for 6 hours
            }

        }
    $resource = $sections->map(function ($section) use ($studentId) {
        return new SectionResource($section, $studentId);
    });

    return $this->success('Sections with videos', [
        'course_data' => [
        'course_id'           => $courseExists->id,
        'course_title'        => $courseExists->title,
        'has_certificate'     => $courseExists->certificate_available,
          'certificate_url' => $courseExists->certificate_available
    ? getOrGeneratePublicCertificateUrl($student, $courseExists)
    : null,
        'is_completed'        => $courseExists->is_completed,
        'progress_percentage' => $courseExists->progress_percentage,
        'has_rated' => Student_rate::where('course_id', $courseExists->id)
                        ->where('student_id', $studentId)
                        ->exists(),
    ],"sections_data"=>$resource]);
}
public function checkCourseAccess($id)
{
    $studentId = auth()->id();
         $student = Auth::user();

    // تحقق من وجود الكورس وكونه مفعلاً ومسجلاً فيه الطالب مع الموافقة
    $course = Course::where('id', $id)
        ->where('is_active', 1)
        ->whereHas('enrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
              ->where('status', 'approved')
              ->where('is_active', 1);
        })
        ->first();

    if (!$course) {
        return $this->failure('Course not found or unauthorized.');
    }

    // إعداد البيانات مثل getVideosBySections
    $courseData = [
        'course_id'           => $course->id,
        'course_title'        => $course->title,
        'has_certificate'     => $course->certificate_available,
         'certificate_url' => $course->certificate_available
    ? getOrGeneratePublicCertificateUrl($student, $course)
    : null,
                     'is_completed'        => $course->is_completed,
        'progress_percentage' => $course->progress_percentage,
        'has_rated'           => Student_rate::where('course_id', $course->id)
                                ->where('student_id', $studentId)
                                ->exists(),
    ];

    return $this->success('Course access data.',
       $courseData
    );
}


public function logWatch(Request $request, $id)
{
    $request->validate([
        'watch_seconds' => 'required|integer|min:1',
        'last_watched_second' => 'nullable|integer|min:0',
    ]);

    $video = CourseVideo::findOrFail($id);
    $student = auth()->user();

    $progress = CourseVideoStudent::firstOrNew([
        'course_video_id' => $video->id,
        'student_id'      => $student->id,
    ]);

    // Initialize previous values if not set
    $previousWatchSeconds = $progress->watch_seconds ?? 0;
    $previousLastSecond = $progress->last_watched_second ?? 0;

    // ✅ لا نسمح بحساب الرجوع للخلف
    $newLastSecond = $request->input('last_watched_second', $previousLastSecond);
    if ($newLastSecond > $previousLastSecond) {
        $progress->last_watched_second = $newLastSecond;
    }

    // ✅ نضيف watch_seconds فقط إذا النقطة الحالية > السابقة
    $watchSeconds = $request->watch_seconds;
    if ($newLastSecond <= $previousLastSecond) {
        $watchSeconds = 0; // لا تضيف أي شيء لو رجع المستخدم
    }

    $progress->watch_seconds = min(
        $previousWatchSeconds + $watchSeconds,
        $video->duration_seconds
    );

    // تحديث وقت المشاهدة
    $progress->last_watched_at = now();

    // ✅ التحقق من الإكمال مرة واحدة فقط
    if (
        !$progress->is_completed &&
        $progress->watch_seconds >= $video->duration_seconds
    ) {
        $progress->is_completed = true;
        $progress->completed_at = now();
        $progress->views = ($progress->views ?? 0) + 1;

        $video->increment('views');
    }

    $progress->save();

    return $this->success('Progress updated', $progress);
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





public function getCourses(Request $request)
{
    $perPage = $request->query('per_page', 10);
    $filter = $request->query('filter'); // values: 'my', 'other', or null
    $today = Carbon::today()->toDateString(); // أو ->now() لو فيه وقت

    if ($filter === 'my' && !Auth::guard('api')->check()) {
        $empty = Course::whereRaw('0=1')->paginate($perPage); // empty pagination
        $resource = CoursesDetailsResource::collection($empty)->response()->getData(true);
        return $this->successWithPagination('No courses found.', $resource);
    }

    $query = Course::query()
        ->where('is_active', 1)
        ->where('is_class', 0)
        ->where('is_enrollment_open', 1)
        ->whereDate('start_date', '<=',  $today)
        ->whereDate('end_date', '>=',  $today);

    if (Auth::guard('api')->check()) {
        $student = Auth::guard('api')->user();

        if ($filter === 'my') {
            $query->whereHas('students', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->where('course_student.status', 'approved')
                  ->where('course_student.is_active', 1);
            });
        } elseif ($filter === 'other') {
            $query->whereDoesntHave('students', function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->where('course_student.status', 'approved')
                  ->where('course_student.is_active', 1);
            });
        }
    }

    // Fetch courses and filter by "not full"
    $courses = $query->get()->filter(fn($course) => !$course->is_full)->values();
     // Paginate manually if needed (simulate Laravel pagination on collections)
    $page = $request->query('page', 1);
    $paginated = collect($courses)->forPage($page, $perPage);
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $paginated,
        $courses->count(),
        $perPage,
        $page,
        ['path' => url()->current(), 'query' => $request->query()]
    );

    $resource = CoursesDetailsResource::collection($paginator)->response()->getData(true);
    return $this->successWithPagination('Courses retrieved successfully.', $resource);
}

public function storerate(Request $request)
{
    $student = auth()->user();
    $studentId = $student->id;

    $data = $request->validate([
        'rate'      => 'required|integer|min:1|max:5',
        'text'      => 'required|string',
        'course_id' => 'required|exists:courses,id',
    ]);

    $course = Course::with('videos')->findOrFail($data['course_id']);

    // ✅ التأكد من تسجيل الطالب في الكورس
    if (!$course->students()->where('student_id', $studentId)->exists()) {
        return $this->failure('أنت لست مسجلاً في هذا الكورس.');
    }

    // ✅ التحقق من إكمال كل الفيديوهات (completion)
    $videoIds = $course->videos->pluck('id');
    $completedCount = \App\Models\CourseVideoStudent::whereIn('course_video_id', $videoIds)
        ->where('student_id', $studentId)
        ->where('is_completed', true)
        ->count();

    if ($course->videos->count() === 0 || $completedCount < $course->videos->count()) {
        return $this->failure('يرجى إكمال مشاهدة جميع فيديوهات الكورس قبل تقديم التقييم.');
    }

    // ✅ التحقق من وجود تقييم سابق لهذا الطالب والكورس
    $existingRate = \App\Models\Student_rate::where('student_id', $studentId)
        ->where('course_id', $data['course_id'])
        ->exists();

    if ($existingRate) {
        return $this->failure('لقد قمت بتقديم تقييم مسبق لهذا الكورس.');
    }

    // ✅ تجهيز بيانات التقييم
    $data['student_id']   = $studentId;
    $data['full_name']    = trim($student->first_name . ' ' . $student->last_name) ?: 'Student';
    $data['image']        = $student->image;
    $data['category_id']  = $student->category_id;

    $rate = \App\Models\Student_rate::create($data);

    return $this->success('شكراً لك على التقييم!');
}


public function getRatesForCourse($course_id)
{
    $studentId = auth()->check() ? auth()->id() : null;

    $course = Course::where('id', $course_id)
        ->first();

    return $this->success('',new CourseStatusResource($course));

}


}
