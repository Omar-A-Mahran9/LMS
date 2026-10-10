<?php

namespace Tests\Feature\Api;

use App\Models\Bundle;
use App\Models\BundleAccessCode;

class BundleAccessTest extends ApiTestCase
{
    private function bundleWithClass(): array
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $this->makeVideo($class);

        $bundle = Bundle::create(['title_ar' => 'باقة', 'title_en' => 'Bundle', 'price' => 100, 'is_active' => 1]);
        $bundle->classes()->attach($class->id);

        return [$bundle, $class];
    }

    private function code(Bundle $bundle, array $attributes = []): BundleAccessCode
    {
        return BundleAccessCode::create(array_merge([
            'bundle_id' => $bundle->id,
            'code' => uniqid('B'),
            'single_use' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => 1,
        ], $attributes));
    }

    public function test_valid_bundle_code_opens_a_class_of_the_bundle(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $code = $this->code($bundle);

        $this->postJson('/api/access_bundle', ['bundle_id' => $bundle->id, 'code' => $code->code])->assertOk();
        $this->getJson("/api/videos_by_bundle_with_code/{$class->id}?code={$code->code}")
            ->assertOk()
            ->assertJsonPath('data.class_data.id', $class->id);
    }

    public function test_bundle_code_does_not_open_classes_outside_the_bundle(): void
    {
        [$bundle] = $this->bundleWithClass();
        $code = $this->code($bundle);
        $otherClass = $this->makeClass($this->makeCourse());

        $this->getJson("/api/videos_by_bundle_with_code/{$otherClass->id}?code={$code->code}")->assertStatus(400);
        $this->assertSame(0, $code->fresh()->used_count);
    }

    public function test_expired_or_not_yet_valid_bundle_codes_are_refused(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $expired = $this->code($bundle, ['expires_at' => now()->subDay()]);
        $future = $this->code($bundle, ['starts_at' => now()->addDay()]);

        foreach ([$expired, $future] as $code) {
            $this->postJson('/api/access_bundle', ['bundle_id' => $bundle->id, 'code' => $code->code])->assertStatus(400);
            $this->getJson("/api/videos_by_bundle_with_code/{$class->id}?code={$code->code}")->assertStatus(400);
            $this->assertSame(0, $code->fresh()->used_count);
        }
    }

    public function test_bundle_code_usage_limit_is_never_exceeded(): void
    {
        [$bundle, $class] = $this->bundleWithClass();
        $code = $this->code($bundle, ['usage_limit' => 1]);

        $this->getJson("/api/videos_by_bundle_with_code/{$class->id}?code={$code->code}")->assertOk();
        $this->getJson("/api/videos_by_bundle_with_code/{$class->id}?code={$code->code}")->assertStatus(400);
        $this->postJson('/api/access_bundle', ['bundle_id' => $bundle->id, 'code' => $code->code])->assertStatus(400);

        $this->assertSame(1, $code->fresh()->used_count);
    }
}
