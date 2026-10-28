<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Slider;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\ApiTestCase;

class SliderOrderTest extends ApiTestCase
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

    private function slider(string $title, int $sortOrder): Slider
    {
        return Slider::create([
            'title_ar' => $title, 'title_en' => $title,
            'btn_title_ar' => 'زر', 'btn_title_en' => 'Btn', 'btn_link' => '/',
            'description_ar' => 'وصف', 'description_en' => 'Desc',
            'background' => '', 'status' => 1, 'sort_order' => $sortOrder,
        ]);
    }

    private function order(): array
    {
        return Slider::pluck('title_en')->all();
    }

    public function test_admin_reorders_the_sliders_and_the_website_follows(): void
    {
        $a = $this->slider('A', 1);
        $b = $this->slider('B', 2);
        $c = $this->slider('C', 3);
        $this->actingAs($this->admin(true), 'admin');

        $this->postJson('/dashboard/sliders/reorder', ['ids' => [$c->id, $a->id, $b->id]])->assertOk();
        $this->assertSame(['C', 'A', 'B'], $this->order());

        // the website's home query (Api\HomeController) follows the same order
        $this->assertSame([$c->id, $a->id, $b->id], Slider::where('status', '1')->pluck('id')->all());
    }

    public function test_reordering_some_rows_keeps_the_others_in_place(): void
    {
        $a = $this->slider('A', 1);
        $b = $this->slider('B', 2);
        $c = $this->slider('C', 3);
        $d = $this->slider('D', 4);
        $this->actingAs($this->admin(true), 'admin');

        // e.g. a search showed only B and D
        $this->postJson('/dashboard/sliders/reorder', ['ids' => [$d->id, $b->id]])->assertOk();
        $this->assertSame(['A', 'D', 'C', 'B'], $this->order());
    }

    public function test_new_slider_goes_last_and_reordering_needs_the_update_ability(): void
    {
        $a = $this->slider('A', 1);
        $b = $this->slider('B', 2);
        $this->assertSame(3, Slider::withoutGlobalScopes()->max('sort_order') + 1);

        $this->actingAs($this->admin(false), 'admin');
        $this->postJson('/dashboard/sliders/reorder', ['ids' => [$b->id, $a->id]])->assertForbidden();
        $this->assertSame(['A', 'B'], $this->order());
    }
}
