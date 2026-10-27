<?php

namespace Tests\Feature\Api;

use App\Models\FreeVideo;

class FreeVideoTest extends ApiTestCase
{
    private function makeFreeVideo(array $attributes = []): FreeVideo
    {
        // The API answers in Arabic by default: give both titles the same text
        $title = $attributes['title_en'] ?? 'Tip';

        return FreeVideo::create(array_merge([
            'title_ar' => $title,
            'title_en' => $title,
            'video_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            'is_active' => true,
        ], $attributes));
    }

    public function test_guests_see_the_active_free_videos_with_the_youtube_id(): void
    {
        $this->makeFreeVideo();
        $this->makeFreeVideo(['title_en' => 'Hidden', 'is_active' => false]);

        $this->getJson('/api/free-videos')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.youtube_id', 'abcdefghijk')
            ->assertJsonPath('data.0.image', 'https://i.ytimg.com/vi/abcdefghijk/hqdefault.jpg')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_grade_filter_keeps_the_videos_for_all_grades(): void
    {
        $grade = $this->makeCategory();
        $other = $this->makeCategory();
        $this->makeFreeVideo(['title_en' => 'Mine', 'category_id' => $grade->id]);
        $this->makeFreeVideo(['title_en' => 'Everyone']);
        $this->makeFreeVideo(['title_en' => 'Other', 'category_id' => $other->id]);

        $titles = collect($this->getJson("/api/free-videos?category_id={$grade->id}&sort=oldest")
            ->assertOk()->json('data'))->pluck('title')->all();

        $this->assertEqualsCanonicalizing(['Mine', 'Everyone'], $titles);
    }

    public function test_search_and_popular_sort(): void
    {
        $this->makeFreeVideo(['title_en' => 'Grammar tip', 'views' => 3]);
        $this->makeFreeVideo(['title_en' => 'Reading tip', 'views' => 10]);
        $this->makeFreeVideo(['title_en' => 'Vocabulary']);

        $this->getJson('/api/free-videos?search=tip&sort=popular')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Reading tip');
    }

    public function test_opening_a_video_counts_a_view_and_returns_related_ones(): void
    {
        $video = $this->makeFreeVideo();
        $this->makeFreeVideo(['title_en' => 'Related']);

        $this->getJson("/api/free-videos/{$video->id}")
            ->assertOk()
            ->assertJsonPath('data.video.id', $video->id)
            ->assertJsonPath('data.video.views', 1)
            ->assertJsonCount(1, 'data.related');

        $this->assertSame(1, (int) $video->fresh()->views);
    }

    public function test_inactive_video_is_not_found(): void
    {
        $video = $this->makeFreeVideo(['is_active' => false]);

        $this->getJson("/api/free-videos/{$video->id}")->assertNotFound();
    }
}
