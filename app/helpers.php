<?php
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Certificate;

if (!function_exists('generateCertificateForStudent')) {
    function generateCertificateForStudent($student, $course)
    {
        $certificateId  = 'CERT-' . strtoupper(Str::random(10));
        $certificateUrl = route('certificates.verify', ['id' => $certificateId]);
        $qrCode         = base64_encode(QrCode::format('png')->size(150)->generate($certificateUrl));

        $pdf = Pdf::loadView('certificates.certificate', [
            'student'        => $student,
            'course'         => $course,
            'qrCode'         => $qrCode,
            'certificateId'  => $certificateId,
            'certificateUrl' => $certificateUrl,
        ])->setPaper('a4', 'landscape');

        $filePath = "certificates/{$certificateId}.pdf";
        $pdf->save(public_path($filePath));

        // ✅ Save to DB
        return Certificate::create([
            'student_id'     => $student->id,
            'course_id'      => $course->id,
            'certificate_id' => $certificateId,
            'file_path'      => $filePath,
            'qr_code'        => $qrCode,
        ]);
    }
}
