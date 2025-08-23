<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\StudentResource;
use App\Http\Resources\Api\UpdateStudentProfileRequest;
use App\Models\Contact_us;
use App\Models\Course;
use App\Models\HomeWork;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
public function profileInfo(Request $request)
{
    $student = $request->user();
    return $this->success('',new StudentResource($student));
}
public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return $this->success(null, 'تم تسجيل الخروج بنجاح');
}
public function logoutFromAllDevices(Request $request)
{
    $user = $request->user();
    $currentTokenId = $user->currentAccessToken()->id;

    // Delete all tokens except the current one
    $user->tokens()->where('id', '!=', $currentTokenId)->delete();

    return $this->success(null, 'تم تسجيل الخروج من جميع الأجهزة ماعدا هذا الجهاز.');
}

public function updatePassword(Request $request)
{
    $request->validate([
        'old_password' => ['required'],
        'password' => ['required', 'min:6', new PasswordNumberAndLetter()],
        'password_confirmation' => ['required_with:password', 'same:password'],
    ]);

    $student = $request->user();

    if (!Hash::check($request->old_password, $student->password)) {
        return $this->validationFailure([
            'old_password' => [__('The old password is incorrect.')],
        ]);
    }

    $student->update([
        'password' => $request->password,
    ]);

  $user = $request->user();
    $currentTokenId = $user->currentAccessToken()->id;

    // Delete all tokens except the current one
    $user->tokens()->where('id', '!=', $currentTokenId)->delete();

    return $this->success(__('Password updated successfully. All other sessions have been logged out.'));
}
public function updateProfileInfo(UpdateStudentProfileRequest $request)
{
    $student = $request->user();

    $data=$request->validated();
     if ($request->hasFile('image')) {
        deleteImageFromDirectory($student->image, 'Students');
        $data['image'] = uploadImageToDirectory($request->file('image'), 'Students');
    }

    $student->update($data);

    return $this->success(__('Profile updated successfully'), new StudentResource($student));
}


public function quizzesResults(Request $request)
{
    $studentId = auth()->id();

    $quizzes = Quiz::with([
            'course:id,title_ar,title_en,is_class',
            'questions:id,quiz_id,points',
            'class:id,title_ar,title_en',
            'section:id,title_ar,title_en'
        ])
        ->whereHas('attempts', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->get()
        ->map(function ($quiz) use ($studentId) {
            $attempts = $quiz->attempts()
                ->where('student_id', $studentId)
                ->get();

            $bestAttempt = $attempts->sortByDesc('score')->first();
            $latestAttempt = $attempts->sortByDesc('started_at')->first();
   $finalAttempt = $bestAttempt && $bestAttempt->submitted_at
                ? $bestAttempt
                : $latestAttempt;
            $totalPoints = $quiz->questions->sum('points') ?: 1;

            $bestScore = $bestAttempt?->score;
            $percentage = $bestScore !== null ? round(($bestScore / $totalPoints) * 100, 2) : null;

            return [
                'attempt_id'       => $bestAttempt?->id,
                'quiz_id'       => $quiz->id,
                'quiz_title'       => $quiz->title,
                'course_title'     => $quiz->course?->title,
                'is_class'=>$quiz->course?->is_class,
                'class_title'      => $quiz->class?->title,
                'section_title'    => $quiz->section?->title,
                'attempt_count'    => $attempts->count(),
                'score'            =>   $bestScore? $bestScore . ' / ' . $totalPoints : __("Not found") ,
                'score_percentage' =>$percentage? $percentage .'%':__("Not found"),
                'last_attempt_at'  => optional($latestAttempt?->started_at)?->format('Y-m-d h:i A'),
                'is_submitted'     => $finalAttempt?->submitted_at !== null,

            ];
        });

    return $this->success('', $quizzes);
}


public function homeworksResults(Request $request)
{
    $studentId = auth()->id();

    $homeworks = HomeWork::with([
          'course:id,title_ar,title_en,is_class',
          'class:id,title_ar,title_en',
          'section:id,title_ar,title_en'
        , 'questions:id,home_work_id,points'])
        ->whereHas('attempts', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->get()
        ->map(function ($homework) use ($studentId) {
            $attempts = $homework->attempts()
                ->where('student_id', $studentId)
                ->get();

            $bestAttempt = $attempts->sortByDesc('score')->first();
            $latestAttempt = $attempts->sortByDesc('started_at')->first();

            $totalPoints = $homework->questions->sum('points') ?: 1;

            $bestScore = $bestAttempt?->score;
            $percentage = $bestScore !== null ? round(($bestScore / $totalPoints) * 100, 2) : null;

            return [
                'attempt_id'       => $bestAttempt?->id,
                'homework_id'   => $homework->id,

                'homework_title'   => $homework->title,
                'course_title'     => $homework->course?->title,
                'is_class'=>$homework->course?->is_class,
                'class_title'      => $homework->class?->title,
                'section_title'    => $homework->section?->title,
                'attempt_count'    => $attempts->count(),
                'score'            => $bestScore . ' / ' . $totalPoints,
                'score_percentage' => $percentage,
                'last_attempt_at'  => optional($latestAttempt?->started_at)?->format('Y-m-d H:i:s'),
                'is_submitted'     => $latestAttempt?->submitted_at !== null,
            ];
        });

    return $this->success('', $homeworks);
}

public function myCourses(Request $request)
{
    $studentId = auth('api')->id();
    $status = $request->query('status'); // completed | in_progress | null

    // Get enrolled courses
    $courses = Course::with(['category:id,name_en,name_ar', 'instructor:id,name', 'videos:id,course_id', 'videos.students' => function ($q) use ($studentId) {
        $q->where('student_id', $studentId);
    }])
        ->whereHas('students', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
                ->where('course_student.status', 'approved')
                ->where('course_student.is_active', 1);
        })
        ->where('is_active', 1)
        ->get()
        ->filter(function ($course) use ($studentId, $status) {
            $totalVideos = $course->videos->count();

            $completedVideos = $course->videos->filter(function ($video) use ($studentId) {
                return $video->students->first()?->pivot?->is_completed ?? false;
            })->count();

            if ($status === 'completed') {
                return $totalVideos > 0 && $completedVideos === $totalVideos;
            } elseif ($status === 'in_progress') {
                return $totalVideos === 0 || $completedVideos < $totalVideos;
            }

            return true; // No filter
        })
        ->values();

    return $this->success('', CoursesDetailsResource::collection($courses));
}

public function studentStatistics()
{
    $user = auth('api')->user();
    $student=Student::find($user->id);
    // جميع المحاولات الخاصة بالطالب
    $attempts = $student->quizAttempts()->with('quiz')->get();

    $totalQuizzes = $attempts->groupBy('quiz_id')->count();
    $successCount = $attempts->filter(function ($attempt) {
        return $attempt->score >= 50; // حسب النجاح المطلوب
    })->groupBy('quiz_id')->count();

    $successRate = $totalQuizzes > 0
        ? round(($successCount / $totalQuizzes) * 100, 2)
        : 0;

    // أعلى محاولة
    $highestAttempt = $attempts->sortByDesc('score')->first();
    $highestScore = $highestAttempt?->score;
    $highestQuizTitle = $highestAttempt?->quiz?->title;

    // أقل محاولة
    $lowestAttempt = $attempts->sortBy('score')->first();
    $lowestScore = $lowestAttempt?->score;
    $lowestQuizTitle = $lowestAttempt?->quiz?->title;

    // متوسط وقت الحل
    $totalSolvingSeconds = $attempts->filter(function ($a) {
        return $a->submitted_at && $a->started_at;
    })->sum(function ($a) {
        return $a->submitted_at->diffInSeconds($a->started_at);
    });

    $solvedCount = $attempts->filter(fn($a) => $a->submitted_at && $a->started_at)->count();
    $totalSolvingTime = $student->quizAttempts()
        ->whereNotNull('started_at')
        ->whereNotNull('submitted_at')
        ->get()
        ->reduce(function ($carry, $attempt) {
            return $carry + $attempt->submitted_at->diffInSeconds($attempt->started_at);
        }, 0);

    $attemptCountForTime = $student->quizAttempts()
        ->whereNotNull('started_at')
        ->whereNotNull('submitted_at')
        ->count();

    $averageSolvingTimeInSeconds = $attemptCountForTime > 0
        ? (int) round($totalSolvingTime / $attemptCountForTime)
        : 0;




    // مقارنة الأداء مع باقي الطلاب في نفس الكورسات
    $enrolledCourseIds = $student->courses()->pluck('courses.id');

    $averageScoresInCourses = QuizAttempt::whereHas('quiz', function ($q) use ($enrolledCourseIds) {
        $q->whereIn('course_id', $enrolledCourseIds);
    })->whereNotNull('score')->avg('score');

    $studentAverageScore = $attempts->avg('score') ?? 0;
    $performancePercentage = $averageScoresInCourses > 0
    ? round(($studentAverageScore / $averageScoresInCourses) * 100, 2)
    : null;


    // تحضير بيانات الرسم البياني
    $chartLabels = [];
    $chartScores = [];
    $maxScores = [];

    foreach ($attempts as $attempt) {
        $quiz = $attempt->quiz;
        if (!$quiz) continue;

        $quizTitle = $quiz->title ?? 'Unnamed Quiz';
        $courseTitle = $quiz->course?->title ?? 'Unknown Course';
        $label = "$quizTitle";

        $chartLabels[] = $label;
        $chartScores[] = $attempt->score ?? 0;
        $maxScores[] = $quiz->questions->sum('points') ?: 100; // الحد الأعلى
    }

    // أعلى درجة من جميع الكويزات لاستخدامها كمقياس Y
    // أعلى درجة من جميع الكويزات لاستخدامها كمقياس Y
    $maxPossibleScore = count($maxScores) > 0 ? max($maxScores) : 100;
    $studentName = $student->first_name ?? 'Student';
    $yAxisSteps = [];
    $step = 15;
    for ($i = 0; $i <= $maxPossibleScore; $i += $step) {
        $yAxisSteps[] = $i;
    }

    return response()->json([
        'success_rate' => [
            'percentage' => $successRate,
            'success_quizzes' => $successCount,
            'total_quizzes' => $totalQuizzes,
        ],
            'highest_score' => [
            'quiz_title' => $highestQuizTitle,
            'score' => $highestScore,
            'total_score' => $highestAttempt?->quiz?->questions->sum('points') ?? 0,
        ],

    'lowest_score' => [
    'quiz_title' => $lowestQuizTitle,
    'score' => $lowestScore,
    'total_score' => $lowestAttempt?->quiz?->questions->sum('points') ?? 0,
    ],

        'timing_comparison'=>[
     'student_average_score' => round($studentAverageScore, 2),
        'overall_average_score_in_courses' => round($averageScoresInCourses, 2),
        ],
        'performance_comparison' => [

        'performance_percentage' => $performancePercentage,
    ],


    'chart_data' => [
    'labels' => $chartLabels,
    'datasets' => [
        [
            'label' => $studentName,
            'backgroundColor' => '#2C3E94', // or use dynamic if needed
            'data' => $chartScores,
        ],
    ],
    ],

    ]);
}

public function myQuestion()
{
    $studentId = auth('api')->id(); // using student guard

    $questions = Contact_us::with('student')
        ->where('student_id', $studentId)
        ->latest()
        ->get()
        ->map(function ($q) {
            return [
                'id'          => $q->id,
                'message'     => $q->message,
                'reply'       => $q->reply && str_ends_with($q->reply, '.mp3')
                                    ? $this->full_audio_path
                                    : $q->reply,
                'is_audio'    => $q->reply && str_ends_with($q->reply, '.mp3'),
                'is_replied'  => !empty($q->reply),
                'created_at'  => $q->created_at->format('Y-m-d H:i'),
                'student'     => [
                    'id'    => $q->student->id,
                    'name'  => $q->student->name,
                    'email' => $q->student->email,
                ],
            ];
        });

    return $this->success("", $questions);
}


}
