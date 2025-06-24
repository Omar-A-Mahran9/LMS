<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseVideoStudent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateController extends Controller
{
    public function download(Course $course)
    {
        $student = Auth::guard('api')->user();

        if (!$student) {
            return response()->json(['message' => __('Unauthorized')], 401);
        }

        // Check course completion
        $videoIds = $course->videos->pluck('id');
        $completedCount = CourseVideoStudent::whereIn('course_video_id', $videoIds)
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->count();

        if ($videoIds->count() > 0 && $completedCount !== $videoIds->count()) {
            return response()->json(['message' => __('You must complete all course videos to receive a certificate.')], 403);
        }

        // Generate QR code (link back to this certificate)
        $qr = base64_encode(QrCode::format('png')->size(120)->generate(url()->current()));

        // Generate PDF certificate
        $pdf = Pdf::loadView('certificates.certificate', [
            'student' => $student,
            'course' => $course,
            'qrCode' => $qr,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("certificate_{$course->id}.pdf");
    }
}
