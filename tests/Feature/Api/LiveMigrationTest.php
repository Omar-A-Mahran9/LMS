<?php

namespace Tests\Feature\Api;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The lives migration (platform as a string + notified_at) rolls back and forward without losing rows.
 * Runs on the in-memory sqlite test database only.
 */
class LiveMigrationTest extends ApiTestCase
{
    private function migration()
    {
        return require database_path('migrations/2026_10_28_000000_update_lives_table_for_live_flow.php');
    }

    private function insertLive(int $courseId, string $platform): int
    {
        return DB::table('lives')->insertGetId([
            'course_id' => $courseId,
            'title_ar' => 'بث',
            'title_en' => 'Live ' . $platform,
            'platform' => $platform,
            'embed_url' => "https://example.test/{$platform}",
            'password' => 'pw-' . $platform,
            'start_time' => '2026-11-05 18:00:00',
            'end_time' => '2026-11-05 19:00:00',
            'is_active' => 1,
        ]);
    }

    public function test_rollback_and_migrate_again_keep_the_rows(): void
    {
        $courseId = $this->makeCourse()->id;
        $youtube = $this->insertLive($courseId, 'youtube');
        $meet = $this->insertLive($courseId, 'google_meet');

        $this->migration()->down();

        $this->assertFalse(Schema::hasColumn('lives', 'notified_at'));
        $this->assertSame(2, DB::table('lives')->count());
        $this->assertSame('youtube', DB::table('lives')->where('id', $youtube)->value('platform'));
        // The old column can't hold Google Meet: it becomes zoom, its link and password stay
        $row = DB::table('lives')->where('id', $meet)->first();
        $this->assertSame('zoom', $row->platform);
        $this->assertSame('https://example.test/google_meet', $row->embed_url);
        $this->assertSame('pw-google_meet', $row->password);

        // The old platform list is enforced again
        try {
            $this->insertLive($courseId, 'facebook');
            $this->fail('The rolled back column accepted a new platform');
        } catch (QueryException) {
        }

        $this->migration()->up();

        $this->assertTrue(Schema::hasColumn('lives', 'notified_at'));
        $this->assertSame(2, DB::table('lives')->count());
        $this->insertLive($courseId, 'facebook');
        $this->assertSame(3, DB::table('lives')->count());
    }
}
