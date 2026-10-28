<?php

namespace Tests\Feature\Api;

use App\Models\Bundle;
use App\Models\BundleAccessCode;
use App\Models\HomeWork;
use App\Models\HomeWorkQuestion;

class BundleAccessTest extends ApiTestCase
{
    private function bundleWithClass(array $attributes = []): array
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $this->makeVideo($class);

        $bundle = Bundle::create(array_merge(['title_ar' => 'باقة', 'title_en' => 'Bundle', 'price' => 100, 'is_active' => 1], $attributes));
        $bundle->classes()->attach($class->id);

        return [$bundle, $class];
    }

    private function code(Bundle $bundle, array $attributes = []): BundleAccessCode
    {
        return BundleAccessCode::create(array_merge([
            'bundle_id' => $bundle->id,
            'code' => uniqid('B'),
            'single_use' => 1,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => 1,
        ], $attributes));
    }

    private function activate(Bundle $bundle, BundleAccessCode $code)
    {
        return $this->postJson('/api/access_bundle', ['bundle_id' => $bundle->id, 'code' => $code->code]);
    }

    public function test_guests_see_the_bundles_on_sale_without_codes(): void
    {
        [$bundle] = $this->bundleWithClass(['discount_price' => 80]);
        $this->bundleWithClass(['title_en' => 'Stopped', 'is_active' => 0]);
        $this->code($bundle);

        $this->getJson('/api/all_bundles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.final_price', 80)
            ->assertJsonPath('data.0.is_owned', false)
            ->assertJsonPath('data.0.classes_count', 1)
            ->assertJsonMissingPath('data.0.codes');

        $this->getJson("/api/bundles/{$bundle->id}")->assertOk()->assertJsonCount(1, 'data.classes');
    }

    public function test_activating_needs_a_logged_in_student(): void
    {
        [$bundle] = $this->bundleWithClass();

        $this->activate($bundle, $this->code($bundle))->assertUnauthorized();
    }

    public function test_single_use_code_activates_once_and_the_student_keeps_the_classes(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $code = $this->code($bundle);
        $this->actingAsStudent($this->makeStudent());

        $this->activate($bundle, $code)->assertOk()->assertJsonPath('data.bundle.is_owned', true);
        $this->assertSame(1, $code->fresh()->used_count);

        // the class opens from the normal lesson page, as many times as needed, without using the code again
        $this->getJson("/api/videos_by_classes/{$class->id}")->assertOk()->assertJsonPath('data.class_data.id', $class->id);
        $this->getJson("/api/videos_by_classes/{$class->id}")->assertOk();
        $this->getJson("/api/videos_by_bundle_with_code/{$class->id}")->assertOk();
        $this->assertSame(1, $code->fresh()->used_count);

        // activating again is harmless
        $this->activate($bundle, $code)->assertOk();
        $this->assertSame(1, $code->fresh()->used_count);

        $this->getJson('/api/all_bundles?mine=1')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_used_code_does_not_work_for_another_student(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $code = $this->code($bundle);
        $this->actingAsStudent($this->makeStudent());
        $this->activate($bundle, $code)->assertOk();

        $this->actingAsStudent($this->makeStudent());
        $this->activate($bundle, $code)->assertStatus(400);
        $this->getJson("/api/videos_by_classes/{$class->id}")->assertStatus(400);
    }

    public function test_bundle_does_not_open_classes_outside_it(): void
    {
        [$bundle] = $this->bundleWithClass();
        $otherClass = $this->makeClass($this->makeCourse());
        $this->actingAsStudent($this->makeStudent());
        $this->activate($bundle, $this->code($bundle))->assertOk();

        $this->getJson("/api/videos_by_classes/{$otherClass->id}")->assertStatus(400);
        $this->getJson("/api/videos_by_bundle_with_code/{$otherClass->id}")->assertStatus(400);
    }

    public function test_code_of_another_bundle_is_refused(): void
    {
        [$bundle] = $this->bundleWithClass();
        [$other] = $this->bundleWithClass();
        $code = $this->code($other);
        $this->actingAsStudent($this->makeStudent());

        $this->activate($bundle, $code)->assertStatus(400);
        $this->assertSame(0, $code->fresh()->used_count);
    }

    public function test_expired_not_yet_valid_or_stopped_bundles_are_refused(): void
    {
        [$bundle] = $this->bundleWithClass();
        $expired = $this->code($bundle, ['expires_at' => now()->subDay()]);
        $future = $this->code($bundle, ['starts_at' => now()->addDay()]);
        $this->actingAsStudent($this->makeStudent());

        foreach ([$expired, $future] as $code) {
            $this->activate($bundle, $code)->assertStatus(400);
            $this->assertSame(0, $code->fresh()->used_count);
        }

        [$stopped] = $this->bundleWithClass(['is_active' => 0]);
        $this->activate($stopped, $this->code($stopped))->assertStatus(400);
    }

    public function test_usage_limit_is_never_exceeded(): void
    {
        [$bundle] = $this->bundleWithClass();
        $code = $this->code($bundle, ['single_use' => 0, 'usage_limit' => 2]);

        foreach (range(1, 3) as $i) {
            $this->actingAsStudent($this->makeStudent());
            $this->activate($bundle, $code)->assertStatus($i <= 2 ? 200 : 400);
        }

        $this->assertSame(2, $code->fresh()->used_count);
    }

    public function test_bundle_owner_can_take_the_class_quiz_and_homework(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $quiz = $this->makeQuiz($class->course, $class);
        $homework = HomeWork::create(['course_id' => $class->course_id, 'class_id' => $class->id, 'title_ar' => 'واجب', 'title_en' => 'HW', 'is_active' => 1]);
        HomeWorkQuestion::create(['home_work_id' => $homework->id, 'question_ar' => 'س', 'question_en' => 'Q', 'type' => 'short_answer', 'points' => 1]);
        $this->actingAsStudent($this->makeStudent());

        $this->getJson("/api/quizzes/{$quiz->id}/start")->assertStatus(400);

        $this->activate($bundle, $this->code($bundle))->assertOk();

        $this->getJson("/api/quizzes/{$quiz->id}/start")->assertOk();
        $this->getJson("/api/homeworks/{$homework->id}/start")->assertOk();
    }
}
