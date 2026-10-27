<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\FreeVideo;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\ApiTestCase;

class FreeVideoDashboardTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RoleSeeder expects admins 1 and 2 and gives role 1 (super admin) every ability
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);
    }

    private function admin(bool $withAllAbilities): Admin
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => uniqid('admin') . '@example.test', 'password' => 'secret123']);
        if ($withAllAbilities) {
            DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        }

        return $admin;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title_ar' => 'نصيحة',
            'title_en' => 'Tip',
            'video_url' => 'https://youtu.be/abcdefghijk',
            'duration_minutes' => 5,
            'is_active' => 1,
            'is_featured' => 1,
        ], $overrides);
    }

    public function test_admin_creates_updates_and_deletes_a_free_video(): void
    {
        $this->actingAs($this->admin(true), 'admin');
        $grade = $this->makeCategory();

        $this->get('/dashboard/free-videos')->assertOk();

        $this->postJson('/dashboard/free-videos', $this->payload(['category_id' => $grade->id]))->assertOk();
        $video = FreeVideo::firstOrFail();
        $this->assertSame(300, (int) $video->duration_seconds);
        $this->assertTrue($video->is_featured);
        $this->assertSame($grade->id, $video->category_id);

        // Switches left off are saved as off, an empty grade means all grades
        // (an unchecked switch is not sent by the form)
        $payload = $this->payload(['title_en' => 'Changed', 'category_id' => null]);
        unset($payload['is_active'], $payload['is_featured']);
        $this->putJson("/dashboard/free-videos/{$video->id}", $payload)->assertOk();
        $video->refresh();
        $this->assertSame('Changed', $video->title_en);
        $this->assertFalse($video->is_active);
        $this->assertFalse($video->is_featured);
        $this->assertNull($video->category_id);

        $this->deleteJson("/dashboard/free-videos/{$video->id}")->assertOk();
        $this->assertDatabaseCount('free_videos', 0);
    }

    public function test_only_youtube_links_are_accepted(): void
    {
        $this->actingAs($this->admin(true), 'admin');

        $this->postJson('/dashboard/free-videos', $this->payload(['video_url' => 'https://vimeo.com/123']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('video_url');
    }

    public function test_admin_without_the_ability_is_refused(): void
    {
        $this->actingAs($this->admin(false), 'admin');

        $this->get('/dashboard/free-videos')->assertForbidden();
        $this->postJson('/dashboard/free-videos', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('free_videos', 0);
    }
}
