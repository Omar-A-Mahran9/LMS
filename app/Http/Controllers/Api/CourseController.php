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
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ClassAccessCode;
use App\Models\ClassAccessLog;
use App\Models\ClassStudent;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Models\CourseVideoStudent;
use App\Models\Government;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Student_rate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller
{
    /**
     * Shared course list filters: search, category_id, price=free|paid|discount, certificate=1,
     * sort=newest|oldest|popular|price_asc|price_desc (default newest).
     */
    private function applyCourseFilters($query, Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $finalPrice = 'CASE WHEN is_free = 1 THEN 0 WHEN have_discount = 1 AND discount_percentage > 0'
            . ' THEN price * (1 - discount_percentage / 100) ELSE price END';

        return $query
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $q->where(fn($w) => $w->where('title_ar', 'like', $like)->orWhere('title_en', 'like', $like));
            })
            ->when($request->integer('category_id'), fn($q, $id) => $q->where('category_id', $id))
            ->when($request->query('price') === 'free', fn($q) => $q->where('is_free', 1))
            ->when($request->query('price') === 'paid', fn($q) => $q->where('is_free', 0))
            ->when($request->query('price') === 'discount', fn($q) => $q->where('is_free', 0)
                ->where('have_discount', 1)->where('discount_percentage', '>', 0))
            ->when($request->boolean('certificate'), fn($q) => $q->where('certificate_available', 1))
            ->when($request->query('sort', 'newest'), function ($q, $sort) use ($finalPrice) {
                match ($sort) {
                    'oldest'     => $q->oldest('id'),
                    'popular'    => $q->orderByDesc('views')->latest('id'),
                    'price_asc'  => $q->orderByRaw("$finalPrice asc")->latest('id'),
                    'price_desc' => $q->orderByRaw("$finalPrice desc")->latest('id'),
                    default      => $q->latest('id'),
                };
            });
    }
    public function getCoursesByCategory(Request $request)
    {
        $categoryId = $request->query('category_id');
        $perPage = $request->query('per_page', 10);
        $filter = $request->query('filter'); // values: 'my', 'other', or null
        if ($filter === 'my' && !Auth::guard('api')->check()) {
            $empty = Course::whereRaw('0=1')->paginate($perPage); // empty pagination
            $resource = CoursesDetailsResource::collection($empty)->response()->getData(true);
            return $this->successWithPagination(__('No courses found.'), $resource);
        }
        $query = Course::query()
            ->where('is_active', 1)
            ->where('is_class', 1)
            ->where('is_enrollment_open', 1);
        // ->whereDate('start_date', '<=', now())
        // ->whereDate('end_date', '>=', now());

        if ($categoryId) {
            $category = Category::find($categoryId);

            if (!$category) {
                return $this->error('Category not found', 404);
            }

            $query->where('category_id', $category->id);
        }

        $this->applyCourseFilters($query, $request);

        if (Auth::guard('api')->check()) {
            $student = Auth::guard('api')->user();

            if ($filter === 'my') {
                $query->whereHas('students', function ($q) use ($student) {
                    $q->where('student_id', $student->id)
                      ->whereIn('course_student.status', ['approved', 'pending'])
                      ->where('course_student.is_active', 1);
                });
            } elseif ($filter === 'other') {
                $query->whereDoesntHave('students', function ($q) use ($student) {
                    $q->where('student_id', $student->id); // just not booked
                });
            }

        }


        $courses = $query->paginate($perPage);
        $resource = CoursesDetailsResource::collection($courses)->response()->getData(true);

        return $this->successWithPagination(__('Courses retrieved successfully.'), $resource);
    }



    public function getCoursesById(Request $request, $id)
    {
        $studentId = auth()->check() ? auth()->id() : null;

        $course = Course::where('id', $id)
            ->where('is_active', 1)
            ->where('is_enrollment_open', 1)
            // ->whereDate('start_date', '<=', now())
            // ->whereDate('end_date', '>=', now())
            ->first();

        if (
            !$course
        ) {
            return $this->failure(__('Course not found or full'));
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
        $studentId = $student?->id;
        if (!$student) {
            return response()->json([
                'message' => __('Unauthorized')
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
            return $this->failure(__('Course not found or unauthorized.'));
        }
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $classes = CourseClass::where('course_id', $id)
        ->where('is_active', 1)
        ->when($search !== '', function ($q) use ($search) {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $q->where(fn($w) => $w->where('title_ar', 'like', $like)->orWhere('title_en', 'like', $like));
        })
        // Course order by default (first class first); newest on request
        ->when($request->query('sort') === 'newest', fn($q) => $q->latest('id'), fn($q) => $q->oldest('id'))
        // A class shows once it has videos, or a live coming before its videos are added
        ->where(function ($q) {
            $q->whereHas('videos', fn($v) => $v->where('is_active', 1))
              ->orWhereHas('lives', fn($l) => $l->where('is_active', 1)->current());
        })
        ->paginate($perPage);

        // A search with no match is an empty list, not an error
        if ($classes->isEmpty() && $search === '') {
            return $this->failure(__('Class not found or unpublished'));
        }

        $resource = ClassesDetailsResource::collection($classes)->response()->getData(true);

        return $this->successWithPagination('', $resource);
    }

    public function getClassById($id)
    {
        $class = CourseClass::where('is_active', 1)->find($id);
        if (!$class) {
            return $this->failure(__('Class not found or unpublished'));
        }

        // The class details include the video links: only for students enrolled in the course
        if ($denied = $this->denyUnlessEnrolled($class->course)) {
            return $denied;
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
            return $this->failure(__('Class not found or unpublished'));
        }

        if ($denied = $this->denyUnlessEnrolled($class->course)) {
            return $denied;
        }

        return $this->success('', new ClassDetailsResource($class));

    }

    public function getQuizById($id)
    {
        $data = Quiz::find($id);
        if (!$data) {
            return $this->failure(__('Quiz not found or unpublished'));
        }

        // The full question paper: only for students enrolled in the course
        if ($denied = $this->denyUnlessEnrolled($data->course)) {
            return $denied;
        }

        return $this->success('', new QuizResource($data));

    }

    // null when the logged-in student has an approved, active enrollment in the course
    private function denyUnlessEnrolled(?Course $course)
    {
        $studentId = auth('api')->id();

        if (!$studentId) {
            return $this->failure(__('Unauthorized'), 401);
        }

        if (!$course || !$course->is_active || !$course->isStudentEnrolled($studentId)) {
            return $this->failure(__('Course not found or unauthorized.'));
        }

        return null;
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
            return $this->failure(__('Class not found or unpublished'));
        }

        // Students enrolled in the course, or who activated a bundle containing this class
        $course = Course::where('id', $class->course_id)
            ->where('is_active', 1)
            ->first();

        // Guests too: classes opened with a code go through videos_by_classes_with_code
        if (!$course || !$studentId
            || (!$course->isStudentEnrolled($studentId) && !Bundle::grantsClass($studentId, $class->id))) {
            return $this->failure(__('Course not found or unauthorized.'));
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


        return $this->success(__('Class videos loaded'), [
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
            'class_data' => new ClassDetailsResource($class, $studentId),
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
            return $this->failure(__('Course not found or unauthorized.'));
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

        return $this->success(__('Sections with videos'), [
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
        ],"sections_data" => $resource]);
    }
    public function checkCourseAccess($id)
    {
        if (auth()) {
            // This route has no auth middleware: read the bearer token explicitly (the default guard is the web session)
            $studentId = auth('api')->id();
            $student = auth('api')->user();

            // تحقق من وجود الكورس وكونه مفعلاً ومسجلاً فيه الطالب مع الموافقة
            $course = Course::where('id', $id)
                ->where('is_active', 1)
                ->whereHas('enrollments', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId)
                      ->where('status', 'approved')
                      ->where('is_active', 1);
                })
                ->first() ?? Course::where('id', $id)
                ->where('is_active', 1)
                ->first();

            if (!$course) {
                return $this->failure(__('Course not found or unauthorized.'));
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
                                        ->exists() ?? null,
            ];

            return $this->success(
                __('Course access data.'),
                $courseData
            );
        }
        $this->success(
            __('Course access data.')
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
        if (!$student) {
            return $this->failure(__('Unauthorized'), 401);
        }

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

        return $this->success(__('Progress updated'), $progress);
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
                'image_url' => getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
                'label'           => setting('label' . $suffix),
                'description'     => setting('about_us' . $suffix),
                'experince_year'     => 20,
                'lecture_count' => CourseVideo::count(),
            ];
        $data = [
            'ask_us' => $ask_us,


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

    public function CoursesList(Request $request)
    {
        $query = Course::where('is_active', 1)
            ->where('is_class', 1);

        // Optional filter by category_id if present
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $courses = $query->get()->map(function ($course) {
            return [
                'id'    => $course->id,
                'title' => $course->title, // Or title_en/title_ar depending on locale
            ];
        });

        return $this->success(__('Courses list retrieved successfully.'), $courses);
    }



    public function getCourses(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $filter = $request->query('filter'); // values: 'my', 'other', or null
        $today = Carbon::today()->toDateString(); // أو ->now() لو فيه وقت

        if ($filter === 'my' && !Auth::guard('api')->check()) {
            $empty = Course::whereRaw('0=1')->paginate($perPage); // empty pagination
            $resource = CoursesDetailsResource::collection($empty)->response()->getData(true);
            return $this->successWithPagination(__('No courses found.'), $resource);
        }

        $query = Course::query()
            ->where('is_active', 1)
            ->where('is_class', 0)
            ->where('is_enrollment_open', 1);
        // ->whereDate('start_date', '<=',  $today)
        // ->whereDate('end_date', '>=',  $today);

        $this->applyCourseFilters($query, $request);

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
        $courses = $query->get()->filter(fn ($course) => !$course->is_full)->values();
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
        return $this->successWithPagination(__('Courses retrieved successfully.'), $resource);
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

        return $this->success('', new CourseStatusResource($course));

    }
    public function getAllClasses(Request $request)
    {
        $perPage    = $request->query('per_page', 10);
        $categoryId = $request->query('category_id');
        $courseId   = $request->query('course_id');
        $search     = trim((string) $request->query('search', ''));
        $sort       = $request->query('sort', 'newest'); // newest | oldest | popular

        $classes = CourseClass::where('is_active', 1)
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('course', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($courseId, function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('title_ar', 'like', $like)
                        ->orWhere('title_en', 'like', $like);
                });
            })
            ->when($sort === 'oldest', fn($query) => $query->oldest('id'))
            ->when($sort === 'popular', fn($query) => $query->orderByDesc('views')->latest('id'))
            ->when(!in_array($sort, ['oldest', 'popular']), fn($query) => $query->latest('id'))
            ->with('course.category')
            ->paginate($perPage);

        return $this->successWithPagination(
            '',
            ClassesDetailsResource::collection($classes)->response()->getData(true)
        );
    }
    public function getAllClassesData(Request $request)
    {
        $categoryId = $request->query('category_id');
        $courseId   = $request->query('course_id');

        $classes = CourseClass::where('is_active', 1)
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('course', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($courseId, function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->get()
            ->map(function ($class) {
                return [
                    'id'    => $class->id,
                    'title' => $class->title, // adjust for multilingual if needed
                ];
            });

        return $this->success(__('Classes list retrieved successfully.'), $classes);
    }

    public function getAllQuizesData(Request $request)
    {
        $classId = $request->query('class_id');

        $quizzes = Quiz::where('is_active', 1)
            ->when($classId, function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->get()
            ->map(function ($quiz) {
                return [
                    'id'    => $quiz->id,
                    'title' => $quiz->title, // adjust if multilingual: $quiz->title_en / title_ar
                ];
            });

        return $this->success(__('Quizzes list retrieved successfully.'), $quizzes);
    }

    public function access(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'code' => 'required|string',
        ]);

        $code = ClassAccessCode::where('class_id', $request->class_id)
            ->where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$code) {
            return $this->failure(__('Invalid or inactive code'));
        }

        // Check usage
        if ($code->single_use && $code->used_count >= 1) {
            return $this->failure(__('This code has already been used'));

        }

        if ($code->usage_limit && $code->used_count >= $code->usage_limit) {
            return $this->failure(__('This access code has expired or reached its usage limit.'));
        }

        // // Log access
        // ClassAccessLog::create([
        //     'student_id' => auth('api')->id(), // or null if not logged in
        //     'class_id' => $request->class_id,
        //     'access_code' => $request->code,
        //     'device_ip' => $request->ip(),
        //     'user_agent' => $request->userAgent(),
        //     'used_at' => now(),
        // ]);

        // // Increment counter
        // $code->increment('used_count');


        return $this->success('تم التحقق من الكود بنجاح', [
        // 'class_url' => env('APP_URL') . '/api/videos_by_classes_with_code/' . $request->class_id,
        ]);


    }


    public function getVideosByClassCode(Request $request, $id)
    {
        $studentId = Auth::id();
        $student = Auth::user();

        $class = CourseClass::where('id', $id)
            ->where('is_active', 1)
            ->first();

        if (!$class) {
            return $this->failure(__('Class not found or unpublished'));
        }

        $course = Course::where('id', $class->course_id)
            ->where('is_active', 1)
            ->first();

        if (!$course) {
            return $this->failure(__('Course not found.'));
        }

        $isAuthorized = false;

        // ✅ Check if student is enrolled
        if ($studentId) {
            $enrolled = $course->enrollments()
                ->where('student_id', $studentId)
                ->where('status', 'approved')
                ->where('is_active', 1)
                ->exists();

            if ($enrolled) {
                $isAuthorized = true;
            }
        }

        // ✅ If not enrolled, check access code
        if (!$isAuthorized && $request->has('code')) {
            $code = $request->get('code');

            $accessCode = ClassAccessCode::where('class_id', $class->id)
                ->where('code', $code)
                ->where('is_active', 1)
                ->first();

            if ($accessCode) {
                // Check usage limits and count this use in one atomic step
                if (!$accessCode->consumeUse()) {
                    return $this->failure(__('This access code has expired or reached its usage limit.'));
                }
                ClassAccessLog::create([
        'student_id' => auth('api')->id(), // or null if not logged in
        'class_id' => $class->id,
        'access_code' => $request->code,
        "access_code_id" => $accessCode->id,
        'device_ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'used_at' => now(),
    ]);
                $isAuthorized = true;
            } else {
                return $this->failure(__('Invalid or inactive access code.'));
            }
        }

        if (!$isAuthorized) {
            return $this->failure(__('Unauthorized access.'));
        }

        // ✅ Track view
        if ($studentId) {
            $cacheKey = "class_viewed_{$class->id}_student_{$studentId}";
            if (!Cache::has($cacheKey)) {
                $class->increment('views');
                Cache::put($cacheKey, true, now()->addHours(6));
            }
        }

        $videos = $class->videos()
            ->where('is_active', 1)
            ->with(['studentProgress' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->get();

        return $this->success(__('Class videos loaded'), [
            'class_data' => new ClassDetailsResource($class, $studentId),
            'videos' => $videos,
        ]);
    }

}
