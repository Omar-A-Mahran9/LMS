<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Category;
use App\Models\ClassAccessCode;
use App\Models\Course;
use App\Models\CourseClass;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\ApiTestCase;

/**
 * An admin publishes a class and an access code from the dashboard, then a student opens it with the code.
 */
class AdminToStudentWorkflowTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_publishes_a_class_with_a_code_and_a_student_opens_it(): void
    {
        $admin = Admin::create(['name' => 'Teacher', 'email' => 'teacher@example.test', 'password' => 'secret123']);
        DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        $this->actingAs($admin, 'admin');

        // Grade
        $this->postJson('/dashboard/categories', [
            'image' => UploadedFile::fake()->image('grade.png'),
            'name_ar' => 'الصف الأول',
            'name_en' => 'First grade',
            'description_ar' => 'وصف الصف',
            'description_en' => 'Grade description',
        ])->assertSuccessful();
        $category = Category::where('name_en', 'First grade')->firstOrFail();

        // Course
        $this->postJson('/dashboard/courses', [
            'image' => UploadedFile::fake()->image('course.png'),
            'slide_image' => UploadedFile::fake()->image('slide.png'),
            'title_ar' => 'كورس الأحياء',
            'title_en' => 'Biology course',
            'description_ar' => 'وصف',
            'description_en' => 'Description',
            'note_ar' => 'ملاحظة',
            'note_en' => 'Note',
            'instructor_id' => $admin->id,
            'is_class' => 1,
            'category_id' => $category->id,
            'duration_per_student' => 30,
            'price' => 100,
            'is_active' => 1,
            'is_enrollment_open' => 1,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ])->assertSuccessful();
        $course = Course::where('title_en', 'Biology course')->firstOrFail();

        // Class + video
        $this->postJson('/dashboard/classes', [
            'image' => UploadedFile::fake()->image('class.png'),
            'course_id' => $course->id,
            'title_ar' => 'الحصة الأولى',
            'title_en' => 'Lesson one',
            'is_active' => 1,
        ])->assertSuccessful();
        $class = CourseClass::where('title_en', 'Lesson one')->firstOrFail();

        $this->postJson('/dashboard/videos', [
            'class_id' => $class->id,
            'course_id' => $course->id,
            'title_ar' => 'فيديو',
            'title_en' => 'Video one',
            'video_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            'duration_seconds' => 600,
            'is_active' => 1,
        ])->assertSuccessful();

        // Access code
        $this->postJson('/dashboard/generateCode', [
            'class_id' => $class->id,
            'code' => 'WELCOME-1',
            'is_active' => 1,
            'usage_limit' => 5,
        ])->assertSuccessful();
        $this->assertDatabaseHas('class_access_codes', ['class_id' => $class->id, 'code' => 'WELCOME-1', 'is_active' => 1]);

        // Student side
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/all_classes')->assertOk()->assertJsonFragment(['id' => $class->id]);
        $this->postJson('/api/access_class', ['class_id' => $class->id, 'code' => 'WELCOME-1'])->assertOk();
        $this->getJson("/api/videos_by_classes_with_code/{$class->id}?code=WELCOME-1")
            ->assertOk()
            ->assertJsonPath('data.class_data.videos.0.title', 'فيديو');

        $this->assertSame(1, ClassAccessCode::where('code', 'WELCOME-1')->value('used_count'));
        $this->assertDatabaseHas('class_access_logs', ['class_id' => $class->id, 'access_code' => 'WELCOME-1']);
    }
}
