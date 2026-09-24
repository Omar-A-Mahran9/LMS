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
    $student = Student::find(auth('api')->id());

    // Only submitted attempts count (an exam still in progress isn't a score of 0)
    $attempts = $student->quizAttempts()
        ->whereNotNull('submitted_at')
        ->with(['quiz' => fn ($q) => $q->withSum('questions as full_mark', 'points')])
        ->orderBy('submitted_at')
        ->get()
        ->filter(fn ($a) => $a->quiz);

    $percentOf = fn ($attempt) => $attempt->quiz->full_mark > 0
        ? round($attempt->score / $attempt->quiz->full_mark * 100, 1)
        : 0;

    // Best attempt per exam, in the order the exams were taken
    $bestPerQuiz = $attempts->groupBy('quiz_id')
        ->map(fn ($quizAttempts) => $quizAttempts->sortByDesc('score')->first())
        ->sortBy('submitted_at')
        ->values();

    // Passing an exam = 50% of its full mark on the best attempt
    $totalQuizzes = $bestPerQuiz->count();
    $successCount = $bestPerQuiz->filter(fn ($a) => $percentOf($a) >= 50)->count();
    $successRate = $totalQuizzes > 0 ? round($successCount / $totalQuizzes * 100, 1) : 0;

    $highestAttempt = $bestPerQuiz->sortByDesc(fn ($a) => $percentOf($a))->first();
    $lowestAttempt = $bestPerQuiz->sortBy(fn ($a) => $percentOf($a))->first();

    // Average solving time in minutes: this student vs everyone who took the same exams
    $minutesExpr = 'TIMESTAMPDIFF(SECOND, started_at, submitted_at) / 60';
    $quizIds = $bestPerQuiz->pluck('quiz_id');
    $studentMinutes = $student->quizAttempts()->whereNotNull('submitted_at')->whereNotNull('started_at')
        ->avg(DB::raw($minutesExpr));
    $overallMinutes = QuizAttempt::whereIn('quiz_id', $quizIds)->whereNotNull('submitted_at')->whereNotNull('started_at')
        ->avg(DB::raw($minutesExpr));

    // Performance: % of classmates (same exams) whose average result is below this student's
    $performancePercentage = null;
    if ($quizIds->isNotEmpty()) {
        $fullMarks = Quiz::whereIn('id', $quizIds)->withSum('questions as full_mark', 'points')->pluck('full_mark', 'id');
        $averages = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->whereNotNull('submitted_at')
            ->get(['student_id', 'quiz_id', 'score'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->avg(fn ($r) => ($fullMarks[$r->quiz_id] ?? 0) > 0 ? $r->score / $fullMarks[$r->quiz_id] * 100 : 0));

        $mine = $averages->pull($student->id);
        if ($mine !== null && $averages->isNotEmpty()) {
            $performancePercentage = round($averages->filter(fn ($avg) => $avg < $mine)->count() / $averages->count() * 100);
        }
    }

    // Chart: result (%) of each exam; short labels for the axis, full details for the tooltip
    $chartPoints = $bestPerQuiz->map(fn ($a) => [
        'title' => $a->quiz->title,
        'short_title' => $this->shortQuizLabel($a->quiz->title),
        'score' => (int) $a->score,
        'total_score' => (int) $a->quiz->full_mark,
        'percentage' => $percentOf($a),
        'date' => $a->submitted_at?->format('Y-m-d'),
    ]);

    return response()->json([
        'success_rate' => [
            'percentage' => $successRate,
            'success_quizzes' => $successCount,
            'total_quizzes' => $totalQuizzes,
        ],
        'highest_score' => [
            'quiz_title' => $highestAttempt?->quiz?->title,
            'score' => $highestAttempt?->score,
            'total_score' => (int) ($highestAttempt?->quiz?->full_mark ?? 0),
        ],
        'lowest_score' => [
            'quiz_title' => $lowestAttempt?->quiz?->title,
            'score' => $lowestAttempt?->score,
            'total_score' => (int) ($lowestAttempt?->quiz?->full_mark ?? 0),
        ],
        // Minutes (the keys keep their old names for the frontend)
        'timing_comparison' => [
            'student_average_score' => round((float) $studentMinutes, 1),
            'overall_average_score_in_courses' => round((float) $overallMinutes, 1),
        ],
        'performance_comparison' => [
            'performance_percentage' => $performancePercentage,
        ],
        'chart_data' => [
            'labels' => $chartPoints->pluck('short_title'),
            'datasets' => [
                [
                    'label' => 'نتيجتك %',
                    'data' => $chartPoints->pluck('percentage'),
                ],
            ],
            'points' => $chartPoints,
        ],
    ]);
}

// "امتحان المحاضره الرابعة (present simple ...)" => "المحاضره الرابعة": readable on the chart axis
private function shortQuizLabel(?string $title): string
{
    $label = trim(preg_replace('/\s+/u', ' ', (string) $title));
    $label = preg_replace('/^امتحان\s+/u', '', $label);
    $label = trim(preg_split('/[(:\-–|]/u', $label)[0]) ?: $label;

    return \Illuminate\Support\Str::limit($label, 24);
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
                                    ? $q->full_audio_path
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
