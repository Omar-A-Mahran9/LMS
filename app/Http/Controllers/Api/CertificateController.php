<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseVideoStudent;
use App\Models\QuizAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateController extends Controller
{
  public function download(Course $course)
    {
        // $student = Auth::guard('api')->user();
        $student = 'omaaar';

        if (!$student) {
            return response()->json(['message' => __('Unauthorized')], 401);
        }

        // // ✅ Check if student is enrolled in this course
        // if (!$course->isStudentEnrolled($student->id)) {
        //     return response()->json(['message' => __('You are not enrolled in this course.')], 403);
        // }

        // // ✅ 1. Check if student completed all course videos
        // $videoIds = $course->videos->pluck('id');
        // $completedVideoCount = CourseVideoStudent::whereIn('course_video_id', $videoIds)
        //     ->where('student_id', $student->id)
        //     ->where('is_completed', true)
        //     ->count();

        // if ($videoIds->count() > 0 && $completedVideoCount !== $videoIds->count()) {
        //     return response()->json(['message' => __('You must complete all course videos to receive a certificate.')], 403);
        // }

        // // ✅ 2. Check if all course quizzes passed with score > 50%
        // $quizIds = $course->quizzes->pluck('id');
        // if ($quizIds->count() > 0) {
        //     $failedQuiz = QuizAttempt::whereIn('quiz_id', $quizIds)
        //         ->where('student_id', $student->id)
        //         ->select('quiz_id', DB::raw('MAX(score) as best_score'))
        //         ->groupBy('quiz_id')
        //         ->havingRaw('best_score < 50')
        //         ->exists();

        //     if ($failedQuiz) {
        //         return response()->json(['message' => __('You must score at least 50% in all quizzes to receive a certificate.')], 403);
        //     }
        // }

        // ✅ 3. Generate QR code
        $qr = base64_encode(QrCode::format('png')->size(120)->generate(url()->current()));

        // ✅ 4. Generate PDF
        $pdf = Pdf::loadView('certificates.certificate', [
            'student' => $student,
            'course' => $course,
            'qrCode' => $qr,
        ])->setPaper('a4', 'landscape');
    return $pdf->stream("certificate_{$course->id}.pdf");
        // return view("certificates.certificate");

    }

}
