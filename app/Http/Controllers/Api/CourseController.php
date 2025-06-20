<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ClassDetailsResource;
use App\Http\Resources\Api\ClassesDetailsResource;
use App\Http\Resources\Api\GovernmentsResource;
 use App\Http\Resources\Api\CourseDetailsResource;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\QuizResource;
use App\Http\Resources\Api\VideoResource;


use App\Models\Category;
use App\Models\ClassStudentView;
use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Models\CourseVideoStudent;
use App\Models\Government;

use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
public function getCoursesByCategory(Request $request)
{
    $categoryId = $request->query('category_id');
    $perPage = $request->query('per_page', 10);
    $filter = $request->query('filter'); // values: 'my', 'other', or null
  if ($filter === 'my' && !Auth::guard('api')->check()) {
        $empty = Course::whereRaw('0=1')->paginate($perPage); // empty pagination
        $resource = CoursesDetailsResource::collection($empty)->response()->getData(true);
        return $this->successWithPagination('No courses found.', $resource);
    }
    $query = Course::query()
        ->where('is_active', 1)
        ->where('is_class', 1)
        ->where('is_enrollment_open', 1)
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now());

    if ($categoryId) {
        $category = Category::find($categoryId);

        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $query->where('category_id', $category->id);
    }

  if (Auth::guard('api')->check()) {
    $student = Auth::guard('api')->user();

if ($filter === 'my') {
    $query->whereHas('students', function ($q) use ($student) {
        $q->where('student_id', $student->id)
          ->whereIn('course_student.status', ['approved', 'pending'])
          ->where('course_student.is_active', 1);
    });
}  elseif ($filter === 'other') {
    $query->whereDoesntHave('students', function ($q) use ($student) {
        $q->where('student_id', $student->id); // just not booked
    });
}

    }


    $courses = $query->paginate($perPage);
    $resource = CoursesDetailsResource::collection($courses)->response()->getData(true);

    return $this->successWithPagination('Courses retrieved successfully.', $resource);
}



   public function getCoursesById($id)
    {
        $course = Course::where('id', $id)
                        ->where('is_active', 1)
                        ->where('is_enrollment_open', 1)
                        ->first();

        if (!$course) {
            return $this->failure('Course not found or unpublished');
        }


        return $this->success('',         new CourseDetailsResource($course));

    }

public function getClassesByCoursesId(Request $request, $id)
{
    $student = auth('api')->user();

    if (!$student) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $isEnrolled = DB::table('course_student')
        ->where('course_id', $id)
        ->where('student_id', $student->id)
        ->exists();

    if (!$isEnrolled) {
        return $this->failure(__("You are not enrolled in this course."));
    }

    $perPage = $request->query('per_page', 10);

    $classes = CourseClass::where('course_id', $id)
                ->where('is_active', 1)
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
        $alreadyViewed = ClassStudentView::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->exists();

        if (!$alreadyViewed) {
            // Log view and increment class views
            ClassStudentView::create([
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

public function getVideosByClass($id)
{
    $class = CourseClass::where('id', $id)
        ->where('is_active', 1)
        ->first();

    if (!$class) {
        return $this->failure('Course not found or unpublished');
    }

    $studentId = Auth::id();

    // Eager load student progress for each video, to avoid N+1 queries
    $videos = $class->videos()
        ->with(['studentProgress' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])
        ->get();

    // Pass student ID to resource collection so it can access progress
    $resourceCollection = $videos->map(function ($video) use ($studentId) {
        return new VideoResource($video, $studentId);
    });

    return $this->success('', $resourceCollection);
}


public function logWatch(Request $request, $id)
{
    $request->validate([
        'watch_seconds' => 'required|integer|min:1',
        'last_watched_second'    => 'nullable|integer|min:0',
    ]);

    $video = CourseVideo::findOrFail($id);
    $student = auth()->user(); // assumes sanctum or jwt

    // Fetch existing or create a new record
    $progress = CourseVideoStudent::firstOrNew([
        'course_video_id' => $video->id,
        'student_id'      => $student->id,
    ]);

    // Handle watch_seconds safely, don't exceed total duration
    $progress->watch_seconds = min(
        ($progress->watch_seconds ?? 0) + $request->watch_seconds,
        $video->duration_seconds
    );

    // Update last_watched_second if provided
    if ($request->filled('last_watched_second')) {
        $progress->last_watched_second = max($request->last_watched_second, $progress->last_watched_second ?? 0);
    }

    // Handle completion
    if (!$progress->is_completed && $progress->watch_seconds >= $video->duration_seconds) {
        $progress->is_completed = true;
        $progress->completed_at = now();
        $progress->views = ($progress->views ?? 0) + 1;

        // Also update global video views count
        $video->increment('views');
    }

    $progress->save();

    return $this->success('',$progress);
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


public function getCourses(Request $request)
{
    $perPage = $request->query('per_page', 10);
    $filter = $request->query('filter'); // values: 'my', 'other', or null

    if ($filter === 'my' && !Auth::guard('api')->check()) {
        $empty = Course::whereRaw('0=1')->paginate($perPage); // empty pagination
        $resource = CoursesDetailsResource::collection($empty)->response()->getData(true);
        return $this->successWithPagination('No courses found.', $resource);
    }

    $query = Course::query()
        ->where('is_active', 1)
        ->where('is_class', 0)
        ->where('is_enrollment_open', 1)
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now());

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



}
