<?php

namespace Tests\Feature\Api;

use App\Models\ClassAccessCode;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Live;
use App\Models\StudentNotification;
use Carbon\Carbon;

/**
 * Live streams: who gets the stream / meeting links and when, the Egypt-time schedule
 * (including the daylight-saving changes) and the notification sent to the grade.
 */
class LiveStreamTest extends ApiTestCase
{
    // Only these keys carry the stream / meeting access
    private const SECRET_KEYS = ['join_url', 'player_url', 'youtube_id', 'meeting_id', 'password', 'chat_embed_url', 'video_embed_url'];

    private const ZOOM_URL = 'https://zoom.us/j/9988776655?pwd=SECRETPWD';

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** "Y-m-d H:i:s" in Egypt time, $minutes from now */
    private function cairo(int $minutes): string
    {
        return now(Live::TIMEZONE)->addMinutes($minutes)->format('Y-m-d H:i:s');
    }

    private function makeLive(Course $course, ?CourseClass $class = null, array $attributes = []): Live
    {
        return Live::create(array_merge([
            'course_id' => $course->id,
            'class_id' => $class?->id,
            'title_ar' => 'بث المراجعة',
            'title_en' => 'Revision live',
            'platform' => 'zoom',
            'embed_url' => self::ZOOM_URL,
            'meeting_id' => '9988776655',
            'password' => 'SECRETPWD',
            'stream_key' => 'STREAMKEY-NEVER-SHARED',
            'start_time' => $this->cairo(60),
            'end_time' => $this->cairo(120),
            'duration_minutes' => 60,
            'is_active' => 1,
            'chat_enabled' => 1,
        ], $attributes));
    }

    /** [course, class] with an approved enrolled student logged in */
    private function enrolledStudentInClass(): array
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $this->makeVideo($class);
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);

        return [$course, $class, $student];
    }

    private function assertNoSecrets(string $json): void
    {
        foreach (['9988776655', 'SECRETPWD', 'zoom.us', 'STREAMKEY-NEVER-SHARED'] as $secret) {
            $this->assertStringNotContainsString($secret, $json, "Response leaks {$secret}");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization / data leakage
    |--------------------------------------------------------------------------
    */

    public function test_public_listing_never_contains_links_or_credentials(): void
    {
        $course = $this->makeCourse();
        $this->makeLive($course, null, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]); // on air
        $this->makeLive($course, null, ['start_time' => $this->cairo(5), 'end_time' => $this->cairo(65)]);  // join window

        $response = $this->getJson('/api/lives')->assertOk()->assertJsonCount(2, 'data');

        $this->assertNoSecrets($response->getContent());
        foreach ($response->json('data') as $live) {
            foreach (self::SECRET_KEYS as $key) {
                $this->assertArrayNotHasKey($key, $live);
            }
        }
    }

    public function test_guest_cannot_open_live_details(): void
    {
        $live = $this->makeLive($this->makeCourse(), null, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]);

        $response = $this->getJson("/api/lives/{$live->id}")->assertStatus(401);
        $this->assertNoSecrets($response->getContent());
    }

    public function test_student_not_enrolled_or_pending_cannot_open_live_details(): void
    {
        $course = $this->makeCourse();
        $live = $this->makeLive($course, null, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]);

        $student = $this->actingAsStudent($this->makeStudent());
        $response = $this->getJson("/api/lives/{$live->id}")->assertStatus(400);
        $this->assertNoSecrets($response->getContent());

        $this->enroll($student, $course, 'pending');
        $response = $this->getJson("/api/lives/{$live->id}")->assertStatus(400);
        $this->assertNoSecrets($response->getContent());
    }

    public function test_enrolled_student_gets_no_links_before_the_join_window(): void
    {
        [$course, $class] = $this->enrolledStudentInClass();
        $live = $this->makeLive($course, $class, ['start_time' => $this->cairo(16), 'end_time' => $this->cairo(76)]);

        $response = $this->getJson("/api/lives/{$live->id}")
            ->assertOk()
            ->assertJsonPath('data.status', Live::STATUS_UPCOMING)
            ->assertJsonPath('data.can_join', false);
        foreach (self::SECRET_KEYS as $key) {
            $response->assertJsonPath("data.{$key}", null);
        }
        $this->assertNoSecrets($response->getContent());

        // The class page carries the same live, without links either
        $response = $this->getJson("/api/videos_by_classes/{$class->id}")
            ->assertOk()
            ->assertJsonPath('data.class_data.live.id', $live->id)
            ->assertJsonPath('data.class_data.live.can_join', false);
        $this->assertNoSecrets($response->getContent());
    }

    public function test_enrolled_student_gets_the_links_inside_the_join_window_and_while_on_air(): void
    {
        [$course, $class] = $this->enrolledStudentInClass();

        foreach ([[14, Live::STATUS_STARTING_SOON], [-30, Live::STATUS_LIVE]] as [$startsIn, $status]) {
            $live = $this->makeLive($course, $class, ['start_time' => $this->cairo($startsIn), 'end_time' => $this->cairo($startsIn + 60)]);

            $this->getJson("/api/lives/{$live->id}")
                ->assertOk()
                ->assertJsonPath('data.status', $status)
                ->assertJsonPath('data.can_join', true)
                ->assertJsonPath('data.join_url', self::ZOOM_URL)
                ->assertJsonPath('data.meeting_id', '9988776655')
                ->assertJsonPath('data.password', 'SECRETPWD')
                ->assertJsonPath('data.player_url', null); // Zoom opens outside the page

            $live->delete();
        }
    }

    public function test_stream_key_is_never_sent_to_students(): void
    {
        [$course, $class] = $this->enrolledStudentInClass();
        $live = $this->makeLive($course, $class, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]);

        foreach (["/api/lives/{$live->id}", "/api/videos_by_classes/{$class->id}", "/api/class/{$class->id}", '/api/lives'] as $uri) {
            $this->assertStringNotContainsString('STREAMKEY-NEVER-SHARED', $this->getJson($uri)->assertOk()->getContent(), $uri);
        }
    }

    public function test_ended_inactive_and_unpublished_lives_give_nothing(): void
    {
        [$course, $class] = $this->enrolledStudentInClass();

        $ended = $this->makeLive($course, $class, ['start_time' => $this->cairo(-120), 'end_time' => $this->cairo(-1)]);
        $response = $this->getJson("/api/lives/{$ended->id}")->assertOk()->assertJsonPath('data.status', Live::STATUS_ENDED);
        $this->assertNoSecrets($response->getContent());
        $this->getJson('/api/lives?course_id=' . $course->id)->assertOk()->assertJsonCount(0, 'data');

        $inactive = $this->makeLive($course, $class, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55), 'is_active' => 0]);
        $this->getJson("/api/lives/{$inactive->id}")->assertNotFound();

        // Live of an unpublished class / course
        $hiddenClass = $this->makeClass($course, ['is_active' => 0]);
        $classLive = $this->makeLive($course, $hiddenClass, ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]);
        $this->getJson("/api/lives/{$classLive->id}")->assertNotFound();

        $course->update(['is_active' => 0]);
        $this->getJson("/api/lives/{$inactive->id}")->assertNotFound();
        $this->getJson('/api/lives')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_class_code_gives_the_live_with_the_same_time_rules(): void
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $this->makeVideo($class);
        ClassAccessCode::create(['class_id' => $class->id, 'code' => 'LIVE-CODE', 'single_use' => 0, 'usage_limit' => 10, 'used_count' => 0, 'is_active' => 1]);

        $live = $this->makeLive($course, $class, ['start_time' => $this->cairo(30), 'end_time' => $this->cairo(90)]);
        $response = $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=LIVE-CODE")
            ->assertOk()
            ->assertJsonPath('data.class_data.live.can_join', false);
        $this->assertNoSecrets($response->getContent());

        $live->update(['start_time' => $this->cairo(-1), 'end_time' => $this->cairo(59)]);
        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=LIVE-CODE")
            ->assertOk()
            ->assertJsonPath('data.class_data.live.join_url', self::ZOOM_URL);

        // Without the code nothing is returned
        $response = $this->getJson("/api/videos_by_classes_with_code/{$class->id}");
        $this->assertNotSame(200, $response->status());
        $this->assertNoSecrets($response->getContent());
    }

    /*
    |--------------------------------------------------------------------------
    | Platforms
    |--------------------------------------------------------------------------
    */

    public function test_youtube_and_facebook_play_inside_the_page_other_platforms_open_outside(): void
    {
        [$course] = $this->enrolledStudentInClass();
        $onAir = ['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)];

        $cases = [
            ['youtube', 'https://www.youtube.com/live/AbCdEfGhIjK?si=share', 'https://www.youtube.com/embed/AbCdEfGhIjK?autoplay=1&rel=0&modestbranding=1', 'AbCdEfGhIjK'],
            ['youtube', 'https://youtu.be/AbCdEfGhIjK', 'https://www.youtube.com/embed/AbCdEfGhIjK?autoplay=1&rel=0&modestbranding=1', 'AbCdEfGhIjK'],
            // A channel's /live page has no video id: falls back to the external button
            ['youtube', 'https://www.youtube.com/@channel/live', null, null],
            ['facebook', 'https://www.facebook.com/page/videos/123', 'https://www.facebook.com/plugins/video.php?show_text=false&href=' . urlencode('https://www.facebook.com/page/videos/123'), null],
            ['google_meet', 'https://meet.google.com/abc-defg-hij', null, null],
            ['twitch', 'https://www.twitch.tv/channel', null, null],
        ];

        foreach ($cases as [$platform, $url, $player, $youtubeId]) {
            $live = $this->makeLive($course, null, $onAir + ['platform' => $platform, 'embed_url' => $url]);

            $this->getJson("/api/lives/{$live->id}")
                ->assertOk()
                ->assertJsonPath('data.player_url', $player)
                ->assertJsonPath('data.youtube_id', $youtubeId)
                ->assertJsonPath('data.join_url', $url);

            $live->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Egypt time and daylight saving
    |--------------------------------------------------------------------------
    */

    public function test_times_are_read_as_egypt_time_not_utc(): void
    {
        // 2026-10-28 is summer time in Egypt (UTC+3)
        Carbon::setTestNow(Carbon::parse('2026-10-28 15:30:00', 'UTC')); // 18:30 in Cairo
        $live = $this->makeLive($this->makeCourse(), null, ['start_time' => '2026-10-28 18:00:00', 'end_time' => '2026-10-28 19:00:00']);

        $this->assertSame(Live::STATUS_LIVE, $live->status);
        $this->assertSame('2026-10-28T18:00:00+03:00', $live->startsAt()->toIso8601String());
        $this->getJson('/api/lives?status=on_air')->assertOk()->assertJsonPath('data.0.id', $live->id);
    }

    public function test_status_follows_the_clock_around_the_join_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00', 'UTC')); // 15:00 in Cairo
        $live = $this->makeLive($this->makeCourse(), null, ['start_time' => '2026-06-01 15:30:00', 'end_time' => '2026-06-01 16:30:00']);

        $at = fn(string $utc) => tap($live->fresh(), fn() => Carbon::setTestNow(Carbon::parse($utc, 'UTC')))->status;

        $this->assertSame(Live::STATUS_UPCOMING, $at('2026-06-01 12:14:59'));
        $this->assertSame(Live::STATUS_STARTING_SOON, $at('2026-06-01 12:15:00'));
        $this->assertSame(Live::STATUS_LIVE, $at('2026-06-01 12:30:00'));
        $this->assertSame(Live::STATUS_LIVE, $at('2026-06-01 13:30:00'));
        $this->assertSame(Live::STATUS_ENDED, $at('2026-06-01 13:30:01'));
    }

    public function test_winter_time_after_daylight_saving_ends(): void
    {
        // Egypt goes back to UTC+2 at 2026-10-29 21:00 UTC (midnight Cairo summer time)
        $live = $this->makeLive($this->makeCourse(), null, ['start_time' => '2026-11-05 18:00:00', 'end_time' => '2026-11-05 19:00:00']);

        $this->assertSame('2026-11-05T18:00:00+02:00', $live->startsAt()->toIso8601String());
        Carbon::setTestNow(Carbon::parse('2026-11-05 16:05:00', 'UTC')); // 18:05 Cairo
        $this->assertSame(Live::STATUS_LIVE, $live->fresh()->status);
        Carbon::setTestNow(Carbon::parse('2026-11-05 15:05:00', 'UTC')); // 17:05 Cairo
        $this->assertSame(Live::STATUS_UPCOMING, $live->fresh()->status);
    }

    public function test_summer_time_after_daylight_saving_starts(): void
    {
        // Egypt moves to UTC+3 at 2026-04-23 22:00 UTC
        $live = $this->makeLive($this->makeCourse(), null, ['start_time' => '2026-04-24 20:00:00', 'end_time' => '2026-04-24 21:00:00']);

        $this->assertSame('2026-04-24T20:00:00+03:00', $live->startsAt()->toIso8601String());
        Carbon::setTestNow(Carbon::parse('2026-04-24 17:10:00', 'UTC')); // 20:10 Cairo
        $this->assertSame(Live::STATUS_LIVE, $live->fresh()->status);
    }

    public function test_repeated_hour_when_daylight_saving_ends(): void
    {
        // 23:00-23:59 on 2026-10-29 happens twice in Cairo. PHP reads these times as the second
        // (winter, UTC+2) pass: the links only open around that pass, never early.
        [$course] = $this->enrolledStudentInClass();
        $live = $this->makeLive($course, null, ['start_time' => '2026-10-29 23:00:00', 'end_time' => '2026-10-29 23:30:00']);
        $this->assertSame('2026-10-29T23:00:00+02:00', $live->startsAt()->toIso8601String());

        Carbon::setTestNow(Carbon::parse('2026-10-29 20:10:00', 'UTC')); // first 23:10 (summer)
        $response = $this->getJson("/api/lives/{$live->id}")
            ->assertOk()
            ->assertJsonPath('data.status', Live::STATUS_UPCOMING)
            ->assertJsonPath('data.can_join', false);
        $this->assertNoSecrets($response->getContent());

        // The join window is still exactly 15 real minutes on that night
        $this->assertSame('2026-10-29T20:45:00+00:00', $live->joinOpensAt()->utc()->toIso8601String());
        Carbon::setTestNow(Carbon::parse('2026-10-29 20:44:59', 'UTC'));
        $this->getJson("/api/lives/{$live->id}")->assertOk()->assertJsonPath('data.can_join', false);
        Carbon::setTestNow(Carbon::parse('2026-10-29 20:45:00', 'UTC'));
        $this->getJson("/api/lives/{$live->id}")->assertOk()->assertJsonPath('data.can_join', true);

        Carbon::setTestNow(Carbon::parse('2026-10-29 21:10:00', 'UTC')); // second 23:10 (winter)
        $this->getJson("/api/lives/{$live->id}")->assertOk()->assertJsonPath('data.can_join', true);

        Carbon::setTestNow(Carbon::parse('2026-10-29 21:31:00', 'UTC')); // second 23:31
        $response = $this->getJson("/api/lives/{$live->id}")->assertOk()->assertJsonPath('data.status', Live::STATUS_ENDED);
        $this->assertNoSecrets($response->getContent());
    }

    public function test_known_limit_lives_crossing_the_clock_change_are_read_as_entered_on_the_wall_clock(): void
    {
        // Known limit (documented): 22:30 -> 23:30 on the autumn night spans the repeated hour,
        // so it really lasts 2 hours; a start inside the skipped spring hour moves 1 hour later.
        $course = $this->makeCourse();
        $autumn = $this->makeLive($course, null, ['start_time' => '2026-10-29 22:30:00', 'end_time' => '2026-10-29 23:30:00']);
        $this->assertSame(120, (int) $autumn->startsAt()->diffInMinutes($autumn->endsAt()));

        $spring = $this->makeLive($course, null, ['start_time' => '2026-04-24 00:30:00', 'end_time' => '2026-04-24 01:30:00']);
        $this->assertSame('2026-04-24T01:30:00+03:00', $spring->startsAt()->toIso8601String());
    }

    /*
    |--------------------------------------------------------------------------
    | Hints (cards / lists)
    |--------------------------------------------------------------------------
    */

    public function test_has_live_follows_the_time_and_a_class_with_only_a_live_is_listed(): void
    {
        $course = $this->makeCourse();
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);
        $liveOnlyClass = $this->makeClass($course);

        // Coming later: listed with the hint, but not "on air"
        $live = $this->makeLive($course, $liveOnlyClass, ['start_time' => $this->cairo(60), 'end_time' => $this->cairo(120)]);
        $this->getJson("/api/classes_by_courses_id/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.0.id', $liveOnlyClass->id)
            ->assertJsonPath('data.0.has_live', false)
            ->assertJsonPath('data.0.live.status', Live::STATUS_UPCOMING);

        // Finished and no videos: the class disappears again
        $live->update(['start_time' => $this->cairo(-120), 'end_time' => $this->cairo(-60)]);
        $this->getJson("/api/classes_by_courses_id/{$course->id}")->assertStatus(400);

        // On air
        $live->update(['start_time' => $this->cairo(-5), 'end_time' => $this->cairo(55)]);
        $this->getJson("/api/classes_by_courses_id/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.0.has_live', true);
    }

    public function test_grade_filter_and_my_lives_filter(): void
    {
        $course = $this->makeCourse();
        $other = $this->makeCourse();
        $mine = $this->makeLive($course, null, ['start_time' => $this->cairo(30), 'end_time' => $this->cairo(90)]);
        $this->makeLive($other, null, ['start_time' => $this->cairo(30), 'end_time' => $this->cairo(90)]);

        $this->getJson('/api/lives?category_id=' . $course->category_id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);

        $this->getJson('/api/lives?mine=1')->assertStatus(401);

        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);
        $this->getJson('/api/lives?mine=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Notification (sent right away when the live is published)
    |--------------------------------------------------------------------------
    */

    public function test_publishing_a_live_notifies_its_grade_once(): void
    {
        $course = $this->makeCourse();
        $inGrade = $this->makeStudent(['category_id' => $course->category_id, 'created_at' => now()->subDay()]);
        $otherGrade = $this->makeStudent(['created_at' => now()->subDay()]);

        // Inactive: nothing yet
        $live = $this->makeLive($course, null, ['is_active' => 0, 'start_time' => $this->cairo(60), 'end_time' => $this->cairo(120)]);
        $this->assertSame(0, StudentNotification::where('type', StudentNotification::TYPE_LIVE)->count());

        // Activated: one notification, saved on the live
        $live->update(['is_active' => 1]);
        $this->assertSame(1, StudentNotification::where('type', StudentNotification::TYPE_LIVE)->count());
        $this->assertNotNull($live->fresh()->notified_at);

        // Turning it off and on again does not repeat it
        $live->update(['is_active' => 0]);
        $live->update(['is_active' => 1]);
        $this->assertSame(1, StudentNotification::where('type', StudentNotification::TYPE_LIVE)->count());

        $this->actingAsStudent($inGrade);
        $this->getJson('/api/notifications')->assertOk()->assertJsonFragment(['type' => 'live']);
        $this->assertNoSecrets($this->getJson('/api/notifications')->getContent());

        $this->actingAsStudent($otherGrade);
        $this->getJson('/api/notifications')->assertOk()->assertJsonMissing(['type' => 'live']);
    }

    public function test_a_live_that_already_ended_is_not_announced(): void
    {
        $this->makeLive($this->makeCourse(), null, ['start_time' => $this->cairo(-120), 'end_time' => $this->cairo(-60)]);

        $this->assertSame(0, StudentNotification::where('type', StudentNotification::TYPE_LIVE)->count());
    }
}
