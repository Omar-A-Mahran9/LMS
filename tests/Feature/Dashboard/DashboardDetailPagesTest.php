<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Bundle;
use App\Models\Section;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\ApiTestCase;

/**
 * Detail pages build links to nested routes (class/section quizzes and homeworks, bundles...):
 * they must keep rendering when routes are changed.
 */
class DashboardDetailPagesTest extends ApiTestCase
{
    public function test_detail_pages_render_for_a_full_admin(): void
    {
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);
        $admin = Admin::create(['name' => 'Teacher', 'email' => 'teacher@example.test', 'password' => 'secret123']);
        DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        $this->actingAs($admin, 'admin');

        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $this->makeVideo($class);
        $this->makeQuiz($course, $class);
        $section = Section::create(['course_id' => $course->id, 'title_en' => 'Section', 'title_ar' => 'قسم', 'is_active' => 1]);
        $bundle = Bundle::create(['title_ar' => 'باقة', 'title_en' => 'Bundle', 'price' => 100, 'is_active' => 1]);
        $student = $this->makeStudent();

        foreach (["classes/{$class->id}", "sections/{$section->id}", "bundles/{$bundle->id}", "students/{$student->id}", "courses/{$course->id}"] as $uri) {
            $this->get("/dashboard/{$uri}")->assertOk();
        }
    }
}
