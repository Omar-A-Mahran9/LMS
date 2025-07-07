<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseVideoStudent;
use App\Models\QuizAttempt;


class CertificateController extends Controller
{
    public function download(Course $course)
    {
        $student = auth('api')->user();
        if (!$student) {
            return $this->failure(__('Unauthorized'));
                }

        // ✅ Check enrollment
        if (!$course->isStudentEnrolled($student->id)) {
            return $this->failure( __('You are not enrolled in this course.') );
        }

        // ✅ Check if all course videos completed
        $videoIds = $course->videos->pluck('id');
        $completedVideos = CourseVideoStudent::whereIn('course_video_id', $videoIds)
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->count();

        if ($videoIds->count() > 0 && $completedVideos < $videoIds->count()) {
            return $this->failure(__('You must complete all course videos to receive a certificate.'));
        }

       // التحقق من النجاح في كل الامتحانات
        $failedQuizzes = [];
        $quizzes = $course->quizzes;

        foreach ($quizzes as $quiz) {
            $totalScore = $quiz->questions()->sum('points');
            $bestScore = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->max('score');

            $required = $totalScore * 0.5;

            if ($bestScore < $required) {
                $failedQuizzes[] = $quiz->title ?? "Quiz ID: {$quiz->id}";
            }
        }

        if (count($failedQuizzes)) {
            return response()->json([
                'message' => __('You must score at least 50% in all quizzes. Retake: ') . implode(', ', $failedQuizzes)
            ], 403);
        }

    // ✅ Check if certificate already exists
    $certificate = Certificate::where('student_id', $student->id)
        ->where('course_id', $course->id)
        ->first();


    if (!$certificate) {
        $certificate = generateCertificateRecord($student, $course); // ONLY creates DB record, no PDF
    }

    // ✅ Stream PDF now (in memory)
    return streamCertificatePdf($student, $course, $certificate);


}
}
