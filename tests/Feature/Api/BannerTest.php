<?php

namespace Tests\Feature\Api;

use App\Models\Banner;

class BannerTest extends ApiTestCase
{
    private function makeBanner(array $attributes = []): Banner
    {
        return Banner::create(array_merge([
            'placement' => 'home_top',
            'title_ar' => 'عرض',
            'audience' => 'all',
            'is_active' => true,
        ], $attributes));
    }

    private function cairo(string $modify): string
    {
        return now(Banner::TIMEZONE)->modify($modify)->format('Y-m-d H:i:s');
    }

    private function titles(string $placement = 'home_top'): array
    {
        return collect($this->getJson('/api/banners')->assertOk()->json("data.$placement"))->pluck('title')->all();
    }

    public function test_banners_are_grouped_by_placement(): void
    {
        $this->makeBanner(['title_ar' => 'Top']);
        $this->makeBanner(['title_ar' => 'Bar', 'placement' => 'top_bar']);

        $this->getJson('/api/banners')
            ->assertOk()
            ->assertJsonStructure(['data' => ['top_bar', 'popup', 'home_top', 'home_middle', 'home_bottom', 'courses_page']])
            ->assertJsonPath('data.home_top.0.title', 'Top')
            ->assertJsonPath('data.top_bar.0.title', 'Bar')
            ->assertJsonCount(0, 'data.popup');
    }

    public function test_only_active_banners_inside_their_dates_show_in_order(): void
    {
        $this->makeBanner(['title_ar' => 'Second', 'sort_order' => 2]);
        $this->makeBanner(['title_ar' => 'First', 'sort_order' => 1, 'starts_at' => $this->cairo('-1 hour'), 'ends_at' => $this->cairo('+1 day')]);
        $this->makeBanner(['title_ar' => 'Off', 'is_active' => false]);
        $this->makeBanner(['title_ar' => 'Later', 'starts_at' => $this->cairo('+1 hour')]);
        $this->makeBanner(['title_ar' => 'Over', 'ends_at' => $this->cairo('-1 minute')]);

        $this->assertSame(['First', 'Second'], $this->titles());
    }

    public function test_guests_and_students_see_their_own_banners(): void
    {
        $student = $this->makeStudent();
        $this->makeBanner(['title_ar' => 'All']);
        $this->makeBanner(['title_ar' => 'Guests', 'audience' => 'guests']);
        $this->makeBanner(['title_ar' => 'Students', 'audience' => 'students']);
        $this->makeBanner(['title_ar' => 'My grade', 'audience' => 'students', 'category_id' => $student->category_id]);
        $this->makeBanner(['title_ar' => 'Other grade', 'category_id' => $this->makeCategory()->id]);

        $this->assertEqualsCanonicalizing(['All', 'Guests'], $this->titles());

        $this->actingAsStudent($student);
        $this->assertEqualsCanonicalizing(['All', 'Students', 'My grade'], $this->titles());
    }

    public function test_countdown_needs_an_end_date_and_sends_it_with_the_cairo_offset(): void
    {
        $this->makeBanner(['show_countdown' => true, 'ends_at' => '2099-01-01 20:00:00']);
        $this->makeBanner(['show_countdown' => true, 'title_ar' => 'No end', 'sort_order' => 1]);

        $this->getJson('/api/banners')
            ->assertJsonPath('data.home_top.0.show_countdown', true)
            ->assertJsonPath('data.home_top.0.ends_at', '2099-01-01T20:00:00+02:00')
            ->assertJsonPath('data.home_top.1.show_countdown', false);
    }

    public function test_clicks_are_counted(): void
    {
        $banner = $this->makeBanner();

        $this->postJson("/api/banners/{$banner->id}/click")->assertOk();
        $this->postJson("/api/banners/{$banner->id}/click")->assertOk();

        $this->assertSame(2, (int) $banner->fresh()->clicks);
    }
}
