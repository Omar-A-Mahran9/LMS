<?php

namespace Tests\Feature\Api;

use App\Models\ClassAccessCode;

class ClassAccessTest extends ApiTestCase
{
    private function paidClassWithVideo(): array
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $video = $this->makeVideo($class);

        return [$course, $class, $video];
    }

    public function test_guest_cannot_load_class_videos_without_enrollment_or_code(): void
    {
        [, $class] = $this->paidClassWithVideo();

        $quiz = $this->makeQuiz($class->course, $class);

        foreach (["videos_by_classes/{$class->id}", "class/{$class->id}", "quiz_by_class_id/{$class->id}", "quiz/{$quiz->id}"] as $uri) {
            $response = $this->getJson("/api/{$uri}");

            $this->assertNotSame(200, $response->status(), "{$uri} is open to guests");
            $this->assertStringNotContainsString(base64_encode('https://www.youtube.com/embed/SECRETVIDEO1'), $response->getContent());
            $this->assertStringNotContainsString('photosynthesis', $response->getContent());
        }
    }

    public function test_logged_in_student_without_enrollment_cannot_load_class_videos(): void
    {
        [, $class] = $this->paidClassWithVideo();
        $this->actingAsStudent($this->makeStudent());

        foreach (["videos_by_classes/{$class->id}", "class/{$class->id}", "quiz_by_class_id/{$class->id}"] as $uri) {
            $this->getJson("/api/{$uri}")->assertStatus(400);
        }
    }

    public function test_pending_enrollment_does_not_grant_access(): void
    {
        [$course, $class] = $this->paidClassWithVideo();
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course, 'pending');

        $this->getJson("/api/videos_by_classes/{$class->id}")->assertStatus(400);
    }

    public function test_enrolled_student_gets_the_class_videos(): void
    {
        [$course, $class, $video] = $this->paidClassWithVideo();
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);

        $this->getJson("/api/videos_by_classes/{$class->id}")
            ->assertOk()
            ->assertJsonPath('data.class_data.videos.0.id', $video->id);

        $this->getJson("/api/class/{$class->id}")->assertOk()->assertJsonPath('data.id', $class->id);
    }

    public function test_class_videos_with_a_valid_code(): void
    {
        [, $class, $video] = $this->paidClassWithVideo();
        ClassAccessCode::create(['class_id' => $class->id, 'code' => 'CODE-1', 'single_use' => 0, 'usage_limit' => 2, 'used_count' => 0, 'is_active' => 1]);

        $this->postJson('/api/access_class', ['class_id' => $class->id, 'code' => 'CODE-1'])->assertOk();

        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=CODE-1")
            ->assertOk()
            ->assertJsonPath('data.class_data.videos.0.id', $video->id);

        $this->assertDatabaseHas('class_access_logs', ['class_id' => $class->id, 'access_code' => 'CODE-1']);
    }

    public function test_invalid_inactive_and_foreign_codes_are_refused(): void
    {
        [, $class] = $this->paidClassWithVideo();
        [, $otherClass] = $this->paidClassWithVideo();
        ClassAccessCode::create(['class_id' => $class->id, 'code' => 'OFF', 'single_use' => 0, 'used_count' => 0, 'is_active' => 0]);
        ClassAccessCode::create(['class_id' => $otherClass->id, 'code' => 'OTHER', 'single_use' => 0, 'used_count' => 0, 'is_active' => 1]);

        foreach (['WRONG', 'OFF', 'OTHER'] as $code) {
            $this->postJson('/api/access_class', ['class_id' => $class->id, 'code' => $code])->assertStatus(400);
            $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code={$code}")->assertStatus(400);
        }
        $this->getJson("/api/videos_by_classes_with_code/{$class->id}")->assertStatus(400);
    }

    public function test_code_usage_limit_is_never_exceeded(): void
    {
        [, $class] = $this->paidClassWithVideo();
        $code = ClassAccessCode::create(['class_id' => $class->id, 'code' => 'LIMIT2', 'single_use' => 0, 'usage_limit' => 2, 'used_count' => 0, 'is_active' => 1]);

        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=LIMIT2")->assertOk();
        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=LIMIT2")->assertOk();
        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=LIMIT2")->assertStatus(400);

        $this->assertSame(2, $code->fresh()->used_count);
    }

    public function test_guest_cannot_log_watch_progress(): void
    {
        [, , $video] = $this->paidClassWithVideo();

        $this->postJson("/api/videos/{$video->id}/watch", ['watch_seconds' => 10, 'last_watched_second' => 10])
            ->assertStatus(401);
    }

    public function test_enrolled_student_watch_progress_is_capped_at_video_length(): void
    {
        [$course, , $video] = $this->paidClassWithVideo();
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);

        $this->postJson("/api/videos/{$video->id}/watch", ['watch_seconds' => 5000, 'last_watched_second' => 100])
            ->assertOk();

        $this->assertDatabaseHas('course_video_student', [
            'student_id' => $student->id,
            'course_video_id' => $video->id,
            'watch_seconds' => 100,
            'is_completed' => 1,
        ]);
    }
}
