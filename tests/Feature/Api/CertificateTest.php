<?php

namespace Tests\Feature\Api;

use App\Models\Certificate;
use App\Models\CourseVideoStudent;
use App\Models\QuizAttempt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // PNG QR codes need the imagick extension, which this machine doesn't have
        QrCode::shouldReceive('format->size->generate')->andReturn('fake-png');
    }

    private function certificateCourse(): array
    {
        $course = $this->makeCourse(['certificate_available' => 1, 'is_class' => 0]);
        $class = $this->makeClass($course);
        $video = $this->makeVideo($class);
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);

        return [$student, $course, $video];
    }

    private function completeVideo($student, $video): void
    {
        CourseVideoStudent::create([
            'student_id' => $student->id,
            'course_video_id' => $video->id,
            'watch_seconds' => $video->duration_seconds,
            'is_completed' => 1,
        ]);
    }

    public function test_course_page_gives_no_certificate_before_the_course_is_completed(): void
    {
        [$student, $course] = $this->certificateCourse();

        $this->getJson("/api/videos_by_course/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.course_data.certificate_url', null);

        $this->assertSame(0, Certificate::where('student_id', $student->id)->count());
    }

    public function test_certificate_requires_passing_the_course_quizzes(): void
    {
        [$student, $course, $video] = $this->certificateCourse();
        $this->completeVideo($student, $video);
        $quiz = $this->makeQuiz($course);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'student_id' => $student->id, 'started_at' => now(), 'submitted_at' => now(), 'score' => 0]);

        $this->getJson("/api/videos_by_course/{$course->id}")->assertJsonPath('data.course_data.certificate_url', null);
        $this->getJson("/api/certificates/{$course->id}")->assertStatus(403);
        $this->assertSame(0, Certificate::where('student_id', $student->id)->count());
    }

    public function test_eligible_student_gets_a_publicly_verifiable_certificate(): void
    {
        [$student, $course, $video] = $this->certificateCourse();
        $this->completeVideo($student, $video);

        $url = $this->getJson("/api/videos_by_course/{$course->id}")->assertOk()->json('data.course_data.certificate_url');
        $this->assertNotNull($url);

        $certificate = Certificate::where('student_id', $student->id)->firstOrFail();

        // Scanning the QR code opens the verify page without being logged in
        $this->app['auth']->forgetGuards();
        $this->get("/api/certificate/verify/{$certificate->certificate_id}")->assertOk();
        $this->get('/api/certificate/verify/CERT-DOESNOTEXIST')->assertNotFound();
    }

    public function test_check_course_does_not_crash_for_certificate_courses(): void
    {
        [, $course] = $this->certificateCourse();

        $this->getJson("/api/check_course/{$course->id}")->assertOk();

        $this->app['auth']->forgetGuards();
        $this->getJson("/api/check_course/{$course->id}", ['Authorization' => ''])->assertOk()
            ->assertJsonPath('data.certificate_url', null);
    }
}
