<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseVideoStudent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function download(Course $course)
    {
        $student = auth('api')->user();

        // Check if student completed course
        $videos = $course->videos;
        $completed = CourseVideoStudent::whereIn('course_video_id', $videos->pluck('id'))
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->count();

        if ($videos->count() === 0 || $completed !== $videos->count()) {
            abort(403, 'You have not completed the course yet.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('certificates.certificate', [
            'student' => $student,
            'course' => $course,
        ]);

        // Optional: save to disk if needed
        // $pdf->save(storage_path("app/certificates/{$student->id}_{$course->id}.pdf"));

        return $pdf->download("certificate_{$course->id}.pdf");
    }
}
