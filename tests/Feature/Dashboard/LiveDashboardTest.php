<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Live;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\ApiTestCase;

/**
 * Creating / editing a live from the dashboard: every field is saved as entered and read back,
 * bad input is refused and only admins with the lives abilities can write.
 */
class LiveDashboardTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // RoleSeeder expects admins 1 and 2
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);
    }

    private function admin(bool $withAllAbilities = true): Admin
    {
        $admin = Admin::create(['name' => 'Teacher', 'email' => uniqid('admin') . '@example.test', 'password' => 'secret123']);
        if ($withAllAbilities) {
            DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        }

        return $admin;
    }

    private function form(array $overrides = []): array
    {
        $course = $overrides['course'] ?? $this->makeCourse();
        unset($overrides['course']);
        $class = $this->makeClass($course);

        // As the form sends it: datetime-local values and "1" switches
        return array_merge([
            'course_id' => $course->id,
            'class_id' => $class->id,
            'title_ar' => 'بث المراجعة النهائية',
            'title_en' => 'Final revision live',
            'description_ar' => '<p>وصف</p>',
            'description_en' => '<p>Description</p>',
            'platform' => 'zoom',
            'embed_url' => 'https://us02web.zoom.us/j/81234567890?pwd=AbC123',
            'meeting_id' => '812 3456 7890',
            'password' => 'AbC123',
            'stream_key' => '',
            'chat_embed_url' => '',
            'start_time' => '2026-11-05T18:00',
            'end_time' => '2026-11-05T19:30',
            'duration_minutes' => '90',
            'chat_enabled' => '1',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_create_saves_every_field_as_entered(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $form = $this->form();

        $this->postJson('/dashboard/lives', $form)->assertOk();

        $live = Live::sole();
        $this->assertSame((int) $form['course_id'], (int) $live->course_id);
        $this->assertSame((int) $form['class_id'], (int) $live->class_id);
        $this->assertSame('zoom', $live->platform);
        $this->assertSame($form['embed_url'], $live->embed_url);
        $this->assertSame('812 3456 7890', $live->meeting_id);
        $this->assertSame('AbC123', $live->password);
        // Egypt time, kept as typed (not shifted to UTC)
        $this->assertSame('2026-11-05 18:00:00', $live->start_time);
        $this->assertSame('2026-11-05 19:30:00', $live->end_time);
        $this->assertSame(90, (int) $live->duration_minutes);
        $this->assertTrue((bool) $live->is_active);
        $this->assertTrue((bool) $live->chat_enabled);
        $this->assertFalse((bool) $live->is_recorded);
        $this->assertSame('2026-11-05T18:00:00+02:00', $live->startsAt()->toIso8601String());
    }

    public function test_end_time_comes_from_the_duration_and_defaults_to_an_hour(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->postJson('/dashboard/lives', $this->form(['end_time' => '', 'duration_minutes' => '45']))->assertOk();
        $this->assertSame('2026-11-05 18:45:00', Live::latest('id')->first()->end_time);

        $this->postJson('/dashboard/lives', $this->form(['end_time' => '', 'duration_minutes' => '']))->assertOk();
        $live = Live::latest('id')->first();
        $this->assertSame('2026-11-05 19:00:00', $live->end_time);
        $this->assertSame(60, (int) $live->duration_minutes);
    }

    public function test_edit_saves_the_new_platform_course_times_and_switches(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->postJson('/dashboard/lives', $this->form())->assertOk();
        $live = Live::sole();

        $newCourse = $this->makeCourse();
        $form = $this->form([
            'course' => $newCourse,
            'platform' => 'google_meet',
            'embed_url' => 'https://meet.google.com/abc-defg-hij',
            'meeting_id' => '',
            'password' => '',
            'start_time' => '2026-11-06T20:15',
            'end_time' => '2026-11-06T21:00',
            'is_active' => null,
            'chat_enabled' => null,
            'is_recorded' => '1',
            '_method' => 'PUT',
        ]);

        $this->postJson("/dashboard/lives/{$live->id}", array_filter($form, fn($v) => $v !== null))->assertOk();

        $live->refresh();
        $this->assertSame($newCourse->id, (int) $live->course_id);
        $this->assertSame((int) $form['class_id'], (int) $live->class_id);
        $this->assertSame('google_meet', $live->platform);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $live->embed_url);
        $this->assertNull($live->meeting_id);
        $this->assertNull($live->password);
        $this->assertSame('2026-11-06 20:15:00', $live->start_time);
        $this->assertSame('2026-11-06 21:00:00', $live->end_time);
        $this->assertSame(45, (int) $live->duration_minutes);
        $this->assertFalse((bool) $live->is_active);
        $this->assertFalse((bool) $live->chat_enabled);
        $this->assertTrue((bool) $live->is_recorded);
    }

    public function test_dashboard_table_reads_back_the_saved_values_for_the_edit_form(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->postJson('/dashboard/lives', $this->form())->assertOk();

        $row = $this->getJson('/dashboard/lives?draw=1&start=0&length=10&columns[0][data]=id&columns[0][search][value]=', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk()->json('data.0');

        // lives.js fills the edit form from these keys
        foreach (['platform' => 'zoom', 'meeting_id' => '812 3456 7890', 'password' => 'AbC123',
                     'start_time' => '2026-11-05 18:00:00', 'end_time' => '2026-11-05 19:30:00'] as $key => $value) {
            $this->assertSame($value, $row[$key], $key);
        }
        $this->assertSame(90, (int) $row['duration_minutes']);
        $this->assertArrayHasKey('status', $row);
        $this->assertArrayNotHasKey('notified_at', $row);
    }

    public function test_invalid_input_is_refused(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $otherCourseClass = $this->makeClass($this->makeCourse());

        $cases = [
            'class_id' => ['class_id' => $otherCourseClass->id],                  // class of another course
            'end_time' => ['end_time' => '2026-11-05T17:00'],                     // ends before it starts
            'platform' => ['platform' => 'skype'],
            'embed_url' => ['embed_url' => 'javascript:alert(document.cookie)'],  // never a script link
            'chat_embed_url' => ['chat_embed_url' => 'not a link'],
            'start_time' => ['start_time' => ''],
        ];

        foreach ($cases as $field => $override) {
            $this->postJson('/dashboard/lives', $this->form($override))
                ->assertStatus(422)
                ->assertJsonValidationErrors($field);
        }
        $this->assertSame(0, Live::count());
    }

    public function test_youtube_needs_the_link_of_the_live_video(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $youtube = ['platform' => 'youtube', 'meeting_id' => '', 'password' => ''];
        $this->postJson('/dashboard/lives', $this->form($youtube + ['embed_url' => 'https://www.youtube.com/@MohamedElnagar/live']))
            ->assertStatus(422)->assertJsonValidationErrors('embed_url');
        $this->postJson('/dashboard/lives', $this->form($youtube + ['embed_url' => 'https://www.youtube.com/live/dQw4w9WgXcQ?si=abc']))
            ->assertOk();

        $this->assertSame('dQw4w9WgXcQ', Live::sole()->youtubeId());
    }

    public function test_search_bulk_delete_and_public_switch(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->postJson('/dashboard/lives', $this->form(['title_en' => 'Algebra live', 'is_public' => '1']))->assertOk();
        $this->postJson('/dashboard/lives', $this->form(['title_en' => 'Grammar live']))->assertOk();
        [$first, $second] = Live::orderBy('id')->get();
        $this->assertTrue($first->is_public);
        $this->assertFalse($second->is_public);

        // The search reads the titles only: the shared password matches nothing
        $search = fn(string $value) => $this->getJson('/dashboard/lives?draw=1&start=0&length=10&columns[0][data]=id&columns[0][search][value]=&search[value]=' . urlencode($value), [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk()->json('data');
        $this->assertSame(['Algebra live'], array_column($search('Algebra'), 'title_en'));
        $this->assertSame([], $search('AbC123'));

        $this->deleteJson('/dashboard/lives/delete-selected', ['selected_items_ids' => [$first->id, $second->id]])->assertOk();
        $this->assertSame(0, Live::count());
    }

    public function test_admin_without_the_lives_abilities_cannot_write(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->postJson('/dashboard/lives', $this->form())->assertOk();
        $live = Live::sole();

        $this->actingAs($this->admin(false), 'admin');
        $this->postJson('/dashboard/lives', $this->form())->assertForbidden();
        $this->postJson("/dashboard/lives/{$live->id}", $this->form(['_method' => 'PUT', 'title_en' => 'Hacked']))->assertForbidden();
        $this->deleteJson("/dashboard/lives/{$live->id}")->assertForbidden();
        $this->getJson('/dashboard/lives', ['X-Requested-With' => 'XMLHttpRequest'])->assertForbidden();

        $this->assertSame('Final revision live', $live->fresh()->title_en);
    }

    public function test_students_cannot_reach_the_dashboard_lives(): void
    {
        $this->actingAsStudent($this->makeStudent());

        $response = $this->getJson('/dashboard/lives', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->assertContains($response->status(), [401, 403, 302]);
        $this->postJson('/dashboard/lives', $this->form())->assertStatus($response->status());
        $this->assertSame(0, Live::count());
    }
}
