<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\CourseVideoStudent;
use App\Models\QuizAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        $quizIds = $course->quizzes->pluck('id');

        if ($quizIds->count() > 0) {
            // اجلب أفضل Score لكل امتحان
            $bestScores = QuizAttempt::whereIn('quiz_id', $quizIds)
                ->where('student_id', $student->id)
                ->select('quiz_id', DB::raw('MAX(score) as best_score'))
                ->groupBy('quiz_id')
                ->pluck('best_score', 'quiz_id'); // [quiz_id => best_score]
dd($bestScores);
            // حدد الامتحانات اللي الطالب لم ينجح فيها (score < 50)
            $failedQuizzes = [];

            foreach ($quizIds as $quizId) {
                $best = $bestScores[$quizId] ?? 0;
                if ($best < 50) {
                    $quiz = $course->quizzes->where('id', $quizId)->first();
                    $failedQuizzes[] = $quiz?->title ?? "Quiz ID: $quizId";
                }
            }

            if (count($failedQuizzes) > 0) {
                return $this->failure(__('لم تنجح في بعض الامتحانات. يرجى إعادة المحاولة في: ') . implode(', ', $failedQuizzes));
            }
        }

        // ✅ Generate Certificate ID & QR Code
        $certificateId = 'CERT-' . strtoupper(Str::random(10));
        $certificateUrl = route('certificates.verify', ['id' => $certificateId]);
        $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($certificateUrl));

        // ✅ Generate PDF
        $pdf = Pdf::loadView('certificates.certificate', [
            'student' => $student,
            'course' => $course,
            'qrCode' => $qrCode,
            'certificateId' => $certificateId,
            'certificateUrl' => $certificateUrl,
        ])->setPaper('a4', 'landscape');

        $filePath = "certificates/{$certificateId}.pdf";
        $pdf->save(public_path($filePath));

        // ✅ Save certificate record in DB
        Certificate::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'certificate_id' => $certificateId,
            'file_path' => $filePath,
        ]);

        // ✅ Return PDF file (or use ->download to force download)
        return response()->file(public_path($filePath));
    }
}
